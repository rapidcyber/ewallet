<?php

namespace App\Merchant\SellerCenter\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingStatus;
use App\Models\InvoiceInclusion;
use App\Models\Merchant;
use App\Models\Notification;
use App\Models\NotificationModule;
use App\Models\Service;
use App\Models\User;
use App\Traits\WithImage;
use App\Traits\WithNotification;
use Database\Factories\InvoiceItemFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

class MerchantSellerCenterServicesBookingsDetails extends Component
{
    use WithImage, WithNotification;

    public Booking $booking;

    public $inclusions;

    public $sub_total;

    public $total;

    public $bookingType;

    public $visible;

    public $actionType;

    public function mount(Merchant $merchant, Service $service, Booking $booking)
    {
        $this->booking = Booking::with(['form_answers', 'media' => function ($q) {
            $q->where('collection_name', 'booking_images');
        }, 'status',
            'entity' => function (MorphTo $q) {
                $q->morphWith([
                    User::class => ['profile', 'media' => function ($q) {
                        $q->where('collection_name', 'profile_picture');
                    }],
                    Merchant::class => ['media' => function ($q) {
                        $q->where('collection_name', 'merchant_logo');
                    }],
                ]);
            }])->findOrFail($booking->id);

        if (! empty($this->booking->invoice)) {
            $discount = $this->booking->invoice->inclusions->where('deduct', true)->sum('amount');
            $surcharge = $this->booking->invoice->inclusions->where('deduct', false)->sum('amount');
            $invoice_items = $this->booking->invoice->items;
            foreach ($invoice_items as $item) {
                $amount = $item->price;
                $quantity = $item->quantity;
                $sub_total = $amount * $quantity;
                $this->sub_total += $sub_total;
            }
            $this->inclusions = $surcharge - $discount;
            $this->total = $this->sub_total + $this->inclusions;
        }
        $this->bookingType = $this->booking->status->slug === 'inquiry' ? 'inquiries' : 'bookings';
    }

    #[Computed(persist: true)]
    public function booking_status()
    {
        return BookingStatus::all();
    }

    public function change_status()
    {
        $booking = $this->booking;

        if (! $booking) {
            session()->flash('error', 'Booking not found.');

            return;
        }

        switch ($this->actionType) {
            case 'accept':
                $this->accept_booking();
                break;
            case 'decline':
                $this->decline();
                break;
            case 'cancel':
                $this->cancel();
                break;
            case 'fulfill':
                $this->fulfill();
                break;
        }

        $this->visible = false;
    }

    public function accept_booking()
    {
        $allowed_status = $this->booking_status->where('slug', 'booked')->first()->id;

        if ($this->booking->booking_status_id === $allowed_status) {
            DB::beginTransaction();
            try {
                $this->booking->booking_status_id = $this->booking_status->where('slug', 'in_progress')->first()->id;
                $this->booking->save();

                $this->booking = $this->booking->load(['entity', 'service']);

                $this->alert(
                    $this->booking->entity,
                    'booking',
                    $this->booking->id,
                    "Your booking for service, {$this->booking->service->name}, has been accepted."
                );

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                Log::error("MerchantSellerCenterServicesBookingsDetails.accept_booking: {$th->getMessage()}");
                return session()->flash('error', 'Something went wrong. Please try again later.');
            }

            session()->flash('success', 'Booking has been accepted.');
            $bookingType = $this->booking->status->slug === 'inquiry' ? 'inquiries' : 'bookings';

            return $this->redirect(route('merchant.seller-center.services.show.bookings.details', [$this->booking->service->merchant, $this->booking->service, $bookingType, $this->booking]));
        } else {
            session()->flash('error', 'Booking must be in "Pending" status to accept.');
        }
    }

    public function fulfill()
    {
        $allowed_status = $this->booking_status->where('slug', 'in_progress')->first()->id;

        if ($this->booking->booking_status_id !== $allowed_status) {
            session()->flash('error', 'Booking must be in "In Progress" status to fulfill.');

            return;
        }

        if ($this->booking->invoice_id == null) {
            return $this->redirect(route('merchant.seller-center.services.show.bookings.quotation.create', [$this->booking->service->merchant, $this->booking->service, 'bookings', $this->booking]));
        } else {
            DB::beginTransaction();
            try {
                $this->booking->booking_status_id = $this->booking_status()->where('slug', 'fulfilled')->first()->id;
                $this->booking->save();
    
                $this->booking = $this->booking->load(['entity', 'service', 'invoice']);

                $invoice = $this->booking->invoice;
                $invoice->type = 'payable';
                $invoice->save();

                $this->alert(
                    $this->booking->entity,
                    'booking',
                    $this->booking->id,
                    "The service booking for {$this->booking->service->name} is now fulfilled. Please proceed with the payment using the provided invoice.",
                );

                DB::commit();
                session()->flash('success', 'Booking has been fulfilled.');
                $bookingType = $this->booking->status->slug === 'inquiry' ? 'inquiries' : 'bookings';
    
                return $this->redirect(route('merchant.seller-center.services.show.bookings.details', [$this->booking->service->merchant, $this->booking->service, $bookingType, $this->booking]));
            } catch (\Exception $ex) {
                DB::rollBack();
                Log::error("MerchantSellerCenterServicesBookingsDetails.fulfill: {$ex->getMessage()}");
                return session()->flash('error', 'Something went wrong. Please try again later.');
            }
        }
    }

    public function decline()
    {
        $this->booking = $this->booking->load(['entity', 'service', 'status']);

        $status = $this->booking->status->slug;

        $allowed_status = $this->booking_status->whereIn('slug', ['inquiry', 'booked'])->pluck('id')->toArray();

        if (in_array($this->booking->booking_status_id, $allowed_status)) {
            DB::beginTransaction();
            try {
                if ($status === 'inquiry') {
                    $module_slug = 'inquiry';
                    $message = "Your inquiry for service, {$this->booking->service->name}, has been declined.";
                } else {
                    $module_slug = 'booking';
                    $message = "Your booking for service, {$this->booking->service->name}, has been declined.";
                }

                $this->booking->booking_status_id = $this->booking_status()->where('slug', 'declined')->first()->id;
                $this->booking->save();
    
                $this->alert(
                    $this->booking->entity,
                    $module_slug,
                    $this->booking->id,
                    $message,
                );

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                Log::error("MerchantSellerCenterServicesBookingsDetails.decline: {$th->getMessage()}");
                return session()->flash('error', 'Something went wrong. Please try again later.');
            }

            session()->flash('success', 'Booking has been declined.');

            $bookingType = $this->booking->status->slug === 'inquiry' ? 'inquiries' : 'bookings';

            return $this->redirect(route('merchant.seller-center.services.show.bookings.details', [$this->booking->service->merchant, $this->booking->service, $bookingType, $this->booking]));
        } else {
            return session()->flash('error', 'Booking must be in "Inquiry", "Quotation Sent", or "Pending" status to decline.');
        }
    }

    public function cancel()
    {
        $allowed_status = $this->booking_status()->where('slug', 'in_progress')->first()->id;

        if ($this->booking->booking_status_id === $allowed_status) {
            DB::beginTransaction();
            try {
                $this->booking->booking_status_id = $this->booking_status()->where('slug', 'cancelled')->first()->id;
                $this->booking->cancelled_by = 'merchant';
                $this->booking->save();
    
                $this->booking = $this->booking->load(['entity', 'service']);

                $this->alert(
                    $this->booking->entity,
                    'booking',
                    $this->booking->id,
                    "Your booking for service, {$this->booking->service->name}, has been cancelled.",
                    
                );

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                Log::error("MerchantSellerCenterServicesBookingsDetails.cancel: {$th->getMessage()}");
                return session()->flash('error', 'Something went wrong. Please try again later.');
            }

            session()->flash('success', 'Booking has been cancelled.');

            $bookingType = $this->booking->status->slug === 'inquiry' ? 'inquiries' : 'bookings';

            return $this->redirect(route('merchant.seller-center.services.show.bookings.details', [$this->booking->service->merchant, $this->booking->service, $bookingType, $this->booking]));

        } else {
            session()->flash('error', 'Booking must be in "In Progress" status to cancel.');
        }
    }

    #[Layout('layouts.merchant.seller-center')]
    public function render()
    {
        return view('merchant.seller-center.services.bookings.merchant-seller-center-services-bookings-details');
    }
}
