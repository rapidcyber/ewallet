<?php

namespace App\Merchant\SellerCenter\Services\Bookings;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Merchant;
use App\Models\Service;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Attributes\Layout;
use Livewire\Component;

class MerchantSellerCenterServicesBookingsQuotation extends Component
{
    public Invoice $invoice;

    public function mount(Merchant $merchant, Service $service, Booking $booking)
    {
        try {
            $this->invoice = Invoice::with(['items', 'inclusions'])->findOrFail($booking->invoice_id);
        } catch (ModelNotFoundException $th) {
            return redirect()->route('merchant.seller-center.services.show.bookings.quotation.create', [$merchant, $service, $booking]);
        }
    }

    #[Layout('layouts.merchant.seller-center')]
    public function render()
    {
        return view('merchant.seller-center.services.bookings.merchant-seller-center-services-bookings-quotation');
    }
}
