<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\BookFromInquiryRequest;
use App\Http\Requests\Service\BookingCancelRequest;
use App\Http\Requests\Service\BookingDetailRequest;
use App\Http\Requests\Service\BookingSendInvoiceRequest;
use App\Http\Requests\Service\BookingUpdateStatusRequest;
use App\Http\Requests\Service\DatesByMonthRequest;
use App\Http\Requests\Service\FulfilledBookingListRequest;
use App\Http\Requests\Service\ListByDateRequest;
use App\Http\Requests\Service\SubmitBookingRequest;
use App\Models\Booking;
use App\Models\BookingStatus;
use App\Models\Invoice;
use App\Models\InvoiceInclusion;
use App\Models\InvoiceItem;
use App\Models\Location;
use App\Models\Service;
use App\Models\User;
use App\Traits\WithEntity;
use App\Traits\WithHttpResponses;
use App\Traits\WithImage;
use App\Traits\WithNotification;
use App\Traits\WithNumberGeneration;
use App\Traits\WithServiceInquiryCheck;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;

class ServiceBookingController extends Controller
{

    use WithHttpResponses, WithEntity, WithServiceInquiryCheck, WithNumberGeneration, WithNotification, WithImage;

    /**
     * Summary of book_from_inquiry
     * @param \App\Http\Requests\Service\BookFromInquiryRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function book_from_inquiry(BookFromInquiryRequest $request)
    {
        $validated = $request->validated();
        $inquiry_id = $validated['inquiry_id'];
        $service_date = $validated['service_date'];
        $time_slots = $validated['time_slots'];

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $inquiry = $entity->service_bookings()
            ->where('id', $inquiry_id)
            ->whereHas('status', function ($q) {
                $q->whereIn('slug', ['inquiry', 'quoted']);
            })->first();

        if (empty($inquiry)) {
            return $this->error('Invalid inquiry ID', 499);
        }

        $slots = $this->check_service_date_slots(
            $inquiry->service,
            Carbon::parse($service_date),
            $time_slots,
        );
        if (is_string($slots)) {
            return $this->error($slots, 499);
        }

        DB::beginTransaction();
        try {
            $inquiry->fill([
                'service_date' => $service_date,
                'slots' => $slots,
                'booking_status_id' => BookingStatus::where('slug', 'booked')->first()->id,
            ]);
            $inquiry->save();

            DB::commit();
            return $this->success();
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }

    /**
     * Summary of book
     * @param \App\Http\Requests\Service\SubmitBookingRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function book(SubmitBookingRequest $request)
    {
        $validated = $request->validated();
        $service_id = $validated['service_id'];
        $service_date = $validated['service_date'];
        $time_slots = $validated['time_slots'];
        $message = $validated['message'];
        $answers = $validated['answers'] ?? null;
        $booking_location = $validated['location'];

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $service = Service::find($service_id);

        $answers_to_save =[];
        if (empty($answers) == false) {
            $answers_to_save = $this->check_answers($service, $answers);
            if (is_string($answers_to_save)) {
                return $this->error($answers_to_save, 499);
            }
        }

        $slots = $this->check_service_date_slots(
            $service,
            Carbon::parse($service_date),
            $time_slots,
        );
        if (is_string($slots)) {
            return $this->error($slots, 499);
        }

        DB::beginTransaction();
        try {
            $booking = new Booking;
            $booking->fill([
                'entity_id' => $entity->id,
                'entity_type' => get_class($entity),
                'service_id' => $service_id,
                'message' => $message,
                'booking_status_id' => BookingStatus::where('slug', 'booked')->first()->id,
                'service_date' => $service_date,
                'slots' => $slots,
            ]);
            $booking->save();
            foreach ($answers_to_save as $answer_to_save) {
                $answer_to_save->booking_id = $booking->id;
                $answer_to_save->save();
            }
            $location = new Location;
            $location->fill([
                'entity_id' => $booking->id,
                'entity_type' => get_class($booking),
                ...$booking_location,
            ]);
            $location->save();

            DB::commit();

            $booking->load(['service:id,name', 'location', 'status']);
            return $this->success($booking);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }

    /**
     * Summary of dates_by_month
     * @param \App\Http\Requests\Service\DatesByMonthRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function dates_by_month(DatesByMonthRequest $request) {
        $validated = $request->validated();
        $service_id = $validated['service_id'] ?? null;
        $month_year = $validated['month_year'];
        $status = $validated['status'];

        $merchant = $this->get(auth()->user(), $validated['merc_ac']);
        if (empty($merchant)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $month_year = Carbon::createFromFormat('Y-m', $month_year);
        $start = $month_year->copy()->firstOfMonth();
        $end = $month_year->copy()->endOfMonth();
        $dates = $merchant->bookings_through_services()
            ->whereHas('status', function ($q) use ($status) {
                $q->where('slug', $status);
            })
            ->where('service_id', $service_id)
            ->whereBetween('service_date', [$start, $end])
            ->pluck('service_date')
            ->toArray();

        $list = [];
        foreach($dates as $date) {
            if (in_array($date, $list) == false) {
                array_push($list, $date);
            }
        }

        return $this->success($list);
    }

    /**
     * Summary of list_by_date
     * @param \App\Http\Requests\Service\ListByDateRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function list_by_date(ListByDateRequest $request) {
        $validated = $request->validated();
        $service_id = $validated['service_id'] ?? null;
        $date = $validated['date'];
        $status = $validated['status'];

        $merchant = $this->get(auth()->user(), $validated['merc_ac']);
        if (empty($merchant)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $bookings = $merchant->bookings_through_services()
            ->whereHas('status', function ($q) use ($status) {
                $q->where('slug', $status);
            })
            ->with(['location', 'service:id,name', 'status', 'entity' => function (MorphTo $query) {
                $query->morphWith([
                    User::class => ['profile']
                ]);
            }])
            ->where('service_id', $service_id)
            ->where('service_date', operator: $date)
            ->get();

        $list = [];
        foreach($bookings as $item) {
            $item = $item->toArray();
            unset($item['entity']['profile']);
            array_push($list, $item);
        }

        return $this->success($list);
    }

    /**
     * Summary of fulfill_service
     * @param \App\Http\Requests\Service\BookingSendInvoiceRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function fulfill_service(BookingSendInvoiceRequest $request) {
        $validated = $request->validated();
        $booking_id = $validated['booking_id'];
        $invoice_id = $validated['invoice_id'] ?? null;

        $invoice_currency = $validated['currency'] ?? 'PHP';
        $invoice_message = $validated['message'] ?? '';
        $invoice_due_date = $validated['due_date'] ?? null;
        $invoice_items = $validated['items'] ?? [];
        $invoice_inclusions = $validated['inclusions'] ?? [];

        $merchant = $this->get(auth()->user(), $validated['merc_ac']);
        if (empty($merchant)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $booking = $merchant->bookings_through_services()
            ->where('bookings.id', $booking_id)
            ->whereHas('status', function ($q) {
                $q->where('slug', 'in_progress');
            })->first();

        if (empty($booking)) {
            return $this->error('Invalid inquiry ID', 499);
        }

        if (empty($invoice_id) == false and $invoice_id != $booking->invoice_id) {
            return $this->error('Invalid booking invoice ID', 499);
        }

        DB::beginTransaction();
        try {
            if (empty($invoice_id) == false) {
                $invoice = $booking->invoice;
                $booking->invoice->type = 'payable';
                $booking->invoice->save();
                $this->alert(
                    $booking->entity,
                    'booking',
                    $booking->id,
                    "The service booking for {$booking->service->name} is now fulfilled. Please proceed with the payment using the provided invoice.",
                );
            } else {
                $invoice = new Invoice;
                $invoice->fill([
                    'sender_id' => $merchant->id,
                    'sender_type' => get_class($merchant),
                    'recipient_id' => $booking->entity_id,
                    'recipient_type' => $booking->entity_type,
                    'invoice_no' => $this->generate_invoice_number(
                        $merchant,
                        $booking->entity
                    ),
                    'currency' => $invoice_currency,
                    'message' => $invoice_message,
                    'due_date' => $invoice_due_date,
                    'type' => 'payable',
                    'minimum_partial' => $validated['minimum_partial'] ?? 0,
                ]);
                $invoice->save();
                foreach ($invoice_items as $item) {
                    $invoice_item = new InvoiceItem;
                    $invoice_item->invoice_id = $invoice->id;
                    $invoice_item->name = $item['name'];
                    $invoice_item->description = $item['description'];
                    $invoice_item->price = $item['price'];
                    $invoice_item->quantity = $item['quantity'];
                    $invoice_item->save();
                }
                foreach ($invoice_inclusions as $inclusion) {
                    $invoice_inclusion = new InvoiceInclusion;
                    $invoice_inclusion->invoice_id = $invoice->id;
                    $invoice_inclusion->name = $inclusion['name'];
                    $invoice_inclusion->amount = $inclusion['amount'];
                    $invoice_inclusion->deduct = $inclusion['deduct'];
                    $invoice_inclusion->save();
                }
                $booking->invoice_id = $invoice->id;
                $this->alert(
                    $booking->entity,
                    'booking',
                    $booking->id,
                    "The service booking for {$booking->service->name} is now fulfilled. Please proceed with the payment using the provided invoice.",
                );
            }

            $booking->booking_status_id = BookingStatus::where('slug', 'fulfilled')->first()->id;
            $booking->save();
            DB::commit();

            $invoice->load(['items', 'inclusions', 'sender', 'recipient']);
            $invoice->inbound = false;
            $invoice->status = 'unpaid';
            $invoice = $invoice->toArray();
            unset(
                $invoice['sender']['profile'],
                $invoice['sender']['status'],
                $invoice['recipient']['profile'],
                $invoice['recipient']['status'],
            );
            return $this->success($invoice);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }


    /**
     * Summary of fulfilled
     * @param \App\Http\Requests\Service\FulfilledBookingListRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function fulfilled(FulfilledBookingListRequest $request) {
        $validated = $request->validated();
        $service_id = $validated['service_id'];
        $invoice_status = $validated['status'];
        $per_page = $validated['per_page'] ?? 10;
        $page = $validated['page'] ?? 1;

        $merchant = $this->get(auth()->user(), $validated['merc_ac']);
        if (empty($merchant)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $bookings = $merchant->bookings_through_services()
            ->select(['bookings.id', 'bookings.service_date'])
            ->where('service_id', $service_id)
            ->whereHas('status', function ($q) {
                $q->where('slug', 'fulfilled');
            })
            ->whereHas('invoice', function ($q)use ($invoice_status) {
                $q->where('status', $invoice_status);
            })
            ->with([
                'invoice:id,invoice_no,due_date,status',
                'entity' => function (MorphTo $query) {
                    $query->morphWith([User::class => ['profile']]);
                }
            ])
            ->paginate(
                $per_page,
                ['*'],
                'bookings',
                $page
            );

        $items = [];
        foreach($bookings->items() as $item) {
            $i = $item->toArray();
            unset($i['entity']['profile'], $i['slots'], $i['message']);
            $items[] = $i;
        }
        return $this->success([
            'bookings' => $items,
            'last_page' => $bookings->lastPage(),
            'total_item' => $bookings->total(),
        ]);
    }

    /**
     * Update status: Accept (in progress) / Decline (merchant)
     * 
     * @param \App\Http\Requests\Service\BookingUpdateStatusRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function booking_respond(BookingUpdateStatusRequest $request) {
        $validated = $request->validated();
        $booking_id = $validated['booking_id'];
        $response = $validated['response'];

        $merchant = $this->get(auth()->user(), $validated['merc_ac']);
        if (empty($merchant)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }
        
        $booking = $merchant->bookings_through_services()
            ->where('bookings.id', $booking_id)
            ->whereHas(
                'status',
                function ($q) {
                    $q->where('slug', 'booked');
                }
            )->first();
        
        if (empty($booking)) {
            return $this->error("Invalid booking ID", 499);
        }

        if ($booking->status->slug == 'in_progress' and $response == 'accept') {
            return $this->error("Booking is already in progress", 499);
        }

        if ($booking->status->slug == 'declined' and $response == 'decline') {
            return $this->error("Booking has already been declined", 499);
        }

        DB::beginTransaction();
        try {
            $status = $response == 'accept' ? 'in_progress': 'declined';
            $booking->booking_status_id = BookingStatus::where('slug', $status)->first()->id;
            
            if ($status == 'declined' and !empty($booking->invoice)) {
                $booking->invoice_id = null;
                $booking->cancelled_by = 'merchant';
                $booking->save();
                
                $booking->invoice->delete();
            }
            
            $booking->save();
            
            $message = "Your booking for service, {$booking->service->name}, has been ";
            if ($status == 'declined') {
                $message .= ' declined.';
            } else {
                $message .= ' accepted.';
            }

            $this->alert(
                $booking->entity,
                'booking',
                $booking->id,
                $message,
            );

            DB::commit();
            $booking->load('status');
            return $this->success($booking->status);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }

    /**
     * Summary of details
     * @param \App\Http\Requests\Service\BookingDetailRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function details(BookingDetailRequest $request)
    {
        $validated = $request->validated();
        // $service_id = $validated['service_id'];
        $booking_id = $validated['booking_id'];

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        // $service = Service::where('id', $service_id)
        //     ->where('approval_status', 'approved')
        //     ->withTrashed()->first();

        // if (empty($service)) {
        //     return $this->error('Invalid service ID', 499);
        // }

        $booking_q = Booking::where('id', $booking_id)
            ->with([
                'status',
                'form_answers',
                'location',
                'entity',
                'service' => function ($q) {
                    $q->select(['id', 'name', 'service_category_id', 'service_days']);
                    $q->with(['category.parent_category']);
                    $q->withTrashed();
                },
                'invoice' => function($q) {
                    $q->with(['sender', 'recipient', 'items', 'inclusions', 'logs'])
                    ->withSum('logs as total_paid', 'amount')
                    ->withSum(['inclusions as additional_amount' => fn($query) => $query->where('deduct', 0)], 'amount')
                    ->withSum(['inclusions as discounted_amount' => fn($query) => $query->where('deduct', 1)], 'amount')
                    ->addSelect(['total_item_price' => InvoiceItem::query()
                        ->whereColumn('invoice_id', 'invoices.id')
                        ->selectRaw('sum(quantity * price) as total_item_price')
                    ]);
                }
            ]);


        $booking = $booking_q->first();
        if (empty($booking)) {
            return $this->error('Invalid inquiry/booking ID', 499);
        }

        $is_mine = $this->is_mine($booking->service, $entity);
        if ($is_mine == false) {
            $booking_q->where('entity_id', $entity->id)->where('entity_type', get_class($entity));
        }

        $this->add_model_images($booking->service, 'service_images', true);
        $booking = $booking->toArray();

        $booking['is_mine'] = $is_mine;
        if (empty($booking['invoice']) == false) {
            $booking['invoice']['inbound'] = !$is_mine;
            unset($booking['invoice']['sender']['profile']);
            unset($booking['invoice']['recipient']['profile']);
        } 
        unset($booking['entity']['profile']);
        return $this->success($booking);
    }


    /**
     * This will be called when a user cancels a booking.
     * 
     * @param \App\Http\Requests\Service\BookingCancelRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function cancel(BookingCancelRequest $request) {

        $validated = $request->validated();
        $booking_id = $validated['booking_id'];

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $booking = $entity->service_bookings()->where('id', $booking_id)
            ->whereHas(
                'status',
                function ($q) {
                    $q->where('slug', 'booked');
                }
            )->first();
        if (empty($booking)) {
            return $this->error('Invalid service or booking ID', 499);
        }

        DB::beginTransaction();
        try {
            $booking->booking_status_id = BookingStatus::where('slug', 'cancelled')->first()->id;
            if (!empty($booking->invoice)) {
                $booking->invoice_id = null;
                $booking->cancelled_by = 'client';
                $booking->save();
                $booking->invoice->delete();
            }            

            $booking->save();
            DB::commit();
            $booking->load('status');
            return $this->success($booking->status);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }

    }
}
