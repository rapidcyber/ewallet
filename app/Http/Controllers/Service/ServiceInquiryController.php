<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\DeleteInquiryRequest;
use App\Http\Requests\Service\InboundListRequest;
use App\Http\Requests\Service\OutboundListRequest;
use App\Http\Requests\Service\SendQuotationRequest;
use App\Http\Requests\Service\ServiceInquireRequest;
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
use App\Traits\WithNotification;
use App\Traits\WithNumberGeneration;
use App\Traits\WithServiceInquiryCheck;
use Exception;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;

class ServiceInquiryController extends Controller
{

    use WithHttpResponses, WithEntity, WithNumberGeneration, WithNotification, WithServiceInquiryCheck;

    /**
     * Summary of inquire
     * @param \App\Http\Requests\Service\ServiceInquireRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function inquire(ServiceInquireRequest $request)
    {
        $validated = $request->validated();
        $service_id = $validated['service_id'];
        $message = $validated['message'];
        $answers = $validated['answers'];
        $inquiry_location = $validated['location'];

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $service = Service::find($service_id);
        $answers_to_save = $this->check_answers($service, $answers);
        if (is_string($answers_to_save)) {
            return $this->error($answers_to_save, 499);
        }

        DB::beginTransaction();
        try {
            /// Create booking with type inquiry
            $inquiry = new Booking;
            $inquiry->fill([
                'entity_id' => $entity->id,
                'entity_type' => get_class($entity),
                'service_id' => $service_id,
                'message' => $message,
                'booking_status_id' => BookingStatus::where('slug', 'inquiry')->first()->id,
            ]);
            $inquiry->save();
            foreach ($answers_to_save as $answer_to_save) {
                $answer_to_save->booking_id = $inquiry->id;
                $answer_to_save->save();
            }
            $location = new Location;
            $location->fill([
                'entity_id' => $inquiry->id,
                'entity_type' => get_class($inquiry),
                ...$inquiry_location,
            ]);
            $location->save();


            // TODO: create notification for merchant

            DB::commit();

            $inquiry->load(['location', 'service:id,name', 'status']);
            return $this->success($inquiry);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }


    /**
     * Summary of delete
     * @param \App\Http\Requests\Service\DeleteInquiryRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function delete(DeleteInquiryRequest $request) {
        $validated = $request->validated();
        $inquiry_id = $validated['inquiry_id'];

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
            return $this->error("Invalid inquiry ID", 499);
        }

        DB::beginTransaction();
        try {
            $inquiry->delete();
            DB::commit();
            return $this->success();
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }

    }

    /**
     * Summary of inbound_list
     * @param \App\Http\Requests\Service\InboundListRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function merchant_list(InboundListRequest $request)
    {
        $validated = $request->validated();
        $status_slug = $validated['status'] ?? null;
        $per_page = $validated['per_page'] ?? 10;
        $page = $validated['page'] ?? 1;
        $service_id = $validated['service_id'] ?? null;

        $merchant = $this->get(auth()->user(), $validated['merc_ac']);
        if (empty($merchant)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $list_q = $merchant->bookings_through_services()
            ->whereDoesntHave('status', function($q) {
                $q->where('slug', 'declined');
            });

        if (empty($service_id) == false) {
            $list_q = $list_q->where('service_id', $service_id);
        }

        if (empty($status_slug) == false) {
            $list_q = $list_q
            ->whereHas(
                'status',
                function ($q) use ($status_slug) {

                    $q->where('slug', $status_slug);
                }
            );
        }
        $list = $list_q->with(['location', 'service:id,name', 'status', 'entity' => function (MorphTo $query) {
                $query->morphWith([
                    User::class => ['profile']
                ]);
            }])
            ->paginate(
                $per_page,
                ['*'],
                'inquiries',
                $page
            );

        $items = [];
        foreach($list->items() as $item) {
            $i = $item->toArray();
            unset($i['entity']['profile']);
            $items[] = $i;
        }

        return $this->success([
            'inquiries' => $items,
            'last_page' => $list->lastPage(),
            'total_item' => $list->total(),
        ]);
    }

    /**
     * Summary of outbound_list
     * @param \App\Http\Requests\Service\OutboundListRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function client_list(OutboundListRequest $request) {
        $validated = $request->validated();
        $status_slug = $validated['status'] ?? null;
        $per_page = $validated['per_page'] ?? 10;
        $page = $validated['page'] ?? 1;
        $service_id = $validated['service_id'] ?? null;

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $list_q = $entity->service_bookings();

        if (empty($service_id) == false) {
            $list_q = $list_q->where('service_id', $service_id);
        }

        if (empty($status_slug) == false) {
            $list_q = $list_q
            ->whereHas(
                'status',
                function ($q) use ($status_slug) {
                    $q->where('slug', $status_slug);
                }
            );
        }

        $list = $list_q->with(['location', 'service:id,name', 'status'])
            ->orderByDesc('service_date')
            ->paginate(
                $per_page,
                ['*'],
                'inquiries',
                $page
            );

        return $this->success([
            'inquiries' => $list->items(),
            'last_page' => $list->lastPage(),
            'total_item' => $list->total(),
        ]);
    }

    /**
     * Summary of send_quotation
     * @param \App\Http\Requests\Service\SendQuotationRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function send_quotation(SendQuotationRequest $request)
    {
        $validated = $request->validated();
        $inquiry_id = $validated['inquiry_id'];
        $invoice_currency = $validated['currency'] ?? 'PHP';
        $invoice_message = $validated['message'] ?? '';
        $invoice_items = $validated['items'];
        $invoice_inclusions = $validated['inclusions'] ?? [];

        $merchant = $this->get(auth()->user(), $validated['merc_ac']);
        if (empty($merchant)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $inquiry = $merchant->bookings_through_services()
            ->where('bookings.id', $inquiry_id)
            ->whereNull('invoice_id')
            ->whereHas('status', function ($q) {
                $q->where('slug', 'inquiry');
            })
            ->first();
        if (empty($inquiry)) {
            return $this->error('Invalid inquiry ID', 499);
        }

        DB::beginTransaction();
        try {
            $quotation = new Invoice;
            $quotation->fill([
                'sender_id' => $merchant->id,
                'sender_type' => get_class($merchant),
                'recipient_id' => $inquiry->entity_id,
                'recipient_type' => $inquiry->entity_type,
                'invoice_no' => $this->generate_invoice_number(
                    $merchant,
                    $inquiry->entity
                ),
                'currency' => $invoice_currency,
                'message' => $invoice_message,
                'due_date' => now(),
                'type' => 'quotation',
            ]);
            $quotation->save();

            foreach ($invoice_items as $item) {
                $invoice_item = new InvoiceItem;
                $invoice_item->invoice_id = $quotation->id;
                $invoice_item->name = $item['name'];
                $invoice_item->description = $item['description'];
                $invoice_item->price = $item['price'];
                $invoice_item->quantity = $item['quantity'];
                $invoice_item->save();
            }

            foreach ($invoice_inclusions as $inclusion) {
                $invoice_inclusion = new InvoiceInclusion;
                $invoice_inclusion->invoice_id = $quotation->id;
                $invoice_inclusion->name = $inclusion['name'];
                $invoice_inclusion->amount = $inclusion['amount'];
                $invoice_inclusion->deduct = $inclusion['deduct'];
                $invoice_inclusion->save();
            }

            $inquiry->invoice_id = $quotation->id;
            $inquiry->booking_status_id = BookingStatus::where(
                'slug',
                'quoted'
            )->first()->id;
            $inquiry->save();

            $this->alert(
                $inquiry->entity,
                'inquiry',
                $quotation->invoice_no,
                "You have received an inquiry quotation from {$merchant->name}.\n\nInvoice No: $quotation->invoice_no",
            );
            DB::commit();

            $quotation->load(['items', 'inclusions', 'sender', 'recipient'])
            ->loadSum('logs as total_paid', 'amount')
            ->loadSum(['inclusions as additional_amount' => fn($query) => $query->where('deduct', 0)], 'amount')
            ->loadSum(['inclusions as discounted_amount' => fn($query) => $query->where('deduct', 1)], 'amount')
            ->addSelect(['total_item_price' => InvoiceItem::query()
                ->whereColumn('invoice_id', 'invoices.id')
                ->selectRaw('sum(quantity * price) as total_item_price')
            ]);

            $quotation->inbound = false;
            $quotation->status = 'unpaid';
            $quotation = $quotation->toArray();
            unset(
                $quotation['sender']['profile'],
                $quotation['sender']['status'],
                $quotation['recipient']['profile'],
                $quotation['recipient']['status'],
            );
            return $this->success($quotation);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }
}
