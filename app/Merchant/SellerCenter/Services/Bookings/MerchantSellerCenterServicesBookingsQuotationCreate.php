<?php

namespace App\Merchant\SellerCenter\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingStatus;
use App\Models\Invoice;
use App\Models\InvoiceInclusion;
use App\Models\InvoiceItem;
use App\Models\Merchant;
use App\Models\Service;
use App\Models\User;
use App\Traits\WithImage;
use App\Traits\WithNotification;
use App\Traits\WithNumberGeneration;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;

class MerchantSellerCenterServicesBookingsQuotationCreate extends Component
{
    use WithNumberGeneration, WithImage, WithNotification;

    public Merchant $merchant;
    public Booking $booking;
    public Service $service;
    public $currency = 'PHP';
    #[Validate('date|date_format:Y-m-d|after_or_equal:today')]
    public $due_date;
    public $allow_vat = false;
    public $allow_discount = false;
    public $allow_shipping = false;
    public $allow_partial = false;
    public $discountAmt = 0;
    public $shippingAmt = 0;
    public $partialAmt = 0;
    public $agree = false;
    public $isAddItemVisible = false;
    public $isEditItemModalVisible = false;
    public $isAddInclusionVisible = false;

    #[Locked]
    public $items = [];
    protected $allowedCurrencyValues = [
        'PHP'
    ];

    public $new_item = [
        'name' => '',
        'description' => '',
        'quantity' => 0,
        'price' => '',
    ];

    public $edit_key;
    public $edit_item = [
        'name' => '',
        'description' => '',
        'quantity' => 0,
        'price' => 0,
    ];
    public $apiErrorMsg = '';
    public $apiSuccessMsg = '';

    public function mount(Merchant $merchant, Service $service, Booking $booking)
    {
        $invoice = Invoice::find($booking->invoice_id);

        if ($invoice) {
            session()->flash('error', 'Invoice/Quotation already exists');
            session()->flash('error_message', 'This booking already has an invoice or quotation.');
            return redirect()->route('merchant.seller-center.services.show.bookings', [$merchant, $service]);
        }

        $this->merchant = $merchant;
        $this->service = $service;
        $this->booking = $booking->load(['status', 'entity' => function (MorphTo $q) {
            $q->morphWith([
                User::class => ['profile'],
                Merchant::class => ['media' => function ($q) {
                    $q->where('collection_name', 'merchant_logo');
                }]
            ]);
        }]);
    }

    #[Computed]
    public function recipient()
    {
        return $this->booking->entity;
    }

    public function handleAddItemModal($boolVal)
    {
        if ($boolVal) {
            $this->new_item = [
                'name' => '',
                'description' => '',
                'price' => '',
                'quantity' => 1,
            ];
        } else {
            $this->reset(['new_item']);
        }
        $this->isAddItemVisible = $boolVal;
    }

    public function handleCommitAdd()
    {
        $this->validate([
            'new_item' => 'array:name,description,quantity,price',
            'new_item.name' => 'required|string|max:255',
            'new_item.description' => 'required|string|max:300',
            'new_item.quantity' => 'required|numeric|min:1',
            'new_item.price' => 'required|numeric|min:1|max:99999999',
        ], [
            'new_item.name.required' => 'Item name is required.',
            'new_item.name.max' => 'Item name must not exceed 255 characters.',
            'new_item.description.required' => 'Item description is required.',
            'new_item.description.max' => 'Item description must not exceed 300 characters.',
            'new_item.quantity.required' => 'Quantity is required.',
            'new_item.quantity.min' => 'Quantity must be at least 1.',
            'new_item.price.required' => 'Price is required.',
            'new_item.price.min' => 'Price must be at least 1.',
            'new_item.price.max' => 'Price must not exceed 99999999.',
        ]);

        array_push($this->items, $this->new_item);
        
        $this->reset(['new_item']);
        $this->isAddItemVisible = false;
    }

    public function handleEditItem(?int $key = null)
    {
        if ($key === null || !isset($this->items[$key])) {
            $this->edit_item = null;
            $this->resetValidation(['edit_item']);
            return;
        }

        $this->edit_key = $key;
        $this->edit_item = $this->items[$key];

        $this->isEditItemModalVisible = true;
    }

    public function handleCommitEdit()
    {
        $this->validate([
            'edit_item' => 'array:name,description,quantity,price',
            'edit_item.name' => 'required|string|max:255',
            'edit_item.description' => 'required|string|max:300',
            'edit_item.quantity' => 'required|numeric|min:1',
            'edit_item.price' => 'required|numeric|min:1|max:99999999',
        ], [
            'edit_item.name.required' => 'Item name is required.',
            'edit_item.name.max' => 'Item name must not exceed 255 characters.',
            'edit_item.description.required' => 'Item description is required.',
            'edit_item.description.max' => 'Item description must not exceed 300 characters.',
            'edit_item.quantity.required' => 'Quantity is required.',
            'edit_item.quantity.min' => 'Quantity must be at least 1.',
            'edit_item.price.required' => 'Price is required.',
            'edit_item.price.min' => 'Price must be at least 1.',
            'edit_item.price.max' => 'Price must not exceed 99999999.',
        ]);

        array_splice($this->items, $this->edit_key, 1, [$this->edit_item]);

        $this->reset(['edit_item']);
        $this->edit_key = null;
        $this->isEditItemModalVisible = false;
    }

    public function handleRemoveItem(int $key)
    {
        unset($this->items[$key]);
    }

    public function getVatAmtProperty()
    {
        if (empty($this->items)) {
            return 0;
        }

        return $this->total_items * .12;
    }

    public function getTotalItemsProperty()
    {
        $totalAmt = 0;
        foreach ($this->items as $item) {
            $totalAmt += $item['price'] * $item['quantity'];
        }

        return $totalAmt;
    }

    public function getInclusionsProperty()
    {
        $val = 0;

        if ($this->allow_vat) {
            $val += $this->vat_amt;
        }

        if ($this->allow_shipping) {
            $val += empty($this->shippingAmt) ? 0 : $this->shippingAmt;
        }

        if ($this->allow_discount) {
            $val -= empty($this->discountAmt) ? 0 : $this->discountAmt;
        }

        return $val;
    }

    public function getTotalProperty()
    {
        return $this->total_items + $this->inclusions;
    }

    public function handleVatValue()
    {
        $this->allow_vat = $this->allow_vat == true;
    }

    public function handleDiscountValue()
    {
        $this->allow_discount = $this->allow_discount == true;
    }

    public function handleShippingValue()
    {
        $this->allow_shipping = $this->allow_shipping == true;
    }

    public function handlePartialValue()
    {
        $this->allow_partial = $this->allow_partial == true;
    }

    public function updatedPartialAmt()
    {
        $this->validate([
            'partialAmt' => 'required_if:partial,1|numeric|min:1|max:' . $this->total,
        ], [
            'partialAmt.required_if' => 'Partial amount is required.',
            'partialAmt.min' => 'Partial amount must be at least 1.',
            'partialAmt.max' => 'Partial amount must not exceed the total.',
        ]);
    }

    public function submit()
    {
        $this->validate([
            'currency' => 'required|in:' . implode(',', $this->allowedCurrencyValues),
            'due_date' => 'required|date|date_format:Y-m-d|after_or_equal:today',
            'items' => 'required|array|min:1',
            'items.*' => 'array:name,description,quantity,price',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'required|string|max:300',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:1|max:99999999',
            'allow_vat' => 'boolean',
            'allow_discount' => 'boolean',
            'allow_shipping' => 'boolean',
            'allow_partial' => 'boolean',
            'discountAmt' => 'nullable|required_if:allow_discount,1|numeric|min:0|max:' . $this->total,
            'shippingAmt' => 'nullable|required_if:allow_shipping,1|numeric|min:0|max:' . $this->total,
            'partialAmt' => 'nullable|required_if:allow_partial,1|numeric|min:0|max:' . $this->total,
            'agree' => 'accepted'
        ]);

        $invoice = new Invoice;
        $invoice->sender_id = $this->merchant->id;
        $invoice->sender_type = Merchant::class;
        $invoice->recipient_id = $this->recipient->id;
        $invoice->recipient_type = get_class($this->recipient);
        $invoice->invoice_no = $this->generate_invoice_number($this->merchant, $this->recipient);
        $invoice->currency = $this->currency;
        $invoice->message = '';
        $invoice->due_date = $this->due_date;
        $invoice->status = 'unpaid';
        $invoice->minimum_partial = $this->allow_partial ? $this->partialAmt : null;
        $invoice->type = 'payable';

        DB::beginTransaction();
        try {
            $invoice->save();

            $this->booking->invoice_id = $invoice->id;

            if ($this->booking->status->slug == 'inquiry') {
                $booking_status = BookingStatus::where('slug', 'quoted')->first();
                $module_slug = 'inquiry';
                $message = "You have received a quotation from {$this->merchant->name} for service, {$this->service->name}.";
            } else {
                $booking_status = BookingStatus::where('slug', 'fulfilled')->first();
                $module_slug = 'booking';
                $message = "The service booking for {$this->booking->service->name} is now fulfilled. Please proceed with the payment using the provided invoice.";
            }

            $this->booking->booking_status_id = $booking_status->id;
            $this->booking->save();

            foreach ($this->items as $item) {
                $invoice_item = new InvoiceItem;
                $invoice_item->invoice_id = $invoice->id;
                $invoice_item->name = $item['name'];
                $invoice_item->description = $item['description'];
                $invoice_item->price = $item['price'];
                $invoice_item->quantity = $item['quantity'];
                $invoice_item->save();
            }

            if ($this->allow_vat) {
                $invoice_inclusion = new InvoiceInclusion;
                $invoice_inclusion->invoice_id = $invoice->id;
                $invoice_inclusion->name = 'vat';
                $invoice_inclusion->amount = $this->vat_amt;
                $invoice_inclusion->deduct = false;
                $invoice_inclusion->save();
            }

            if ($this->allow_discount) {
                $invoice_inclusion = new InvoiceInclusion;
                $invoice_inclusion->invoice_id = $invoice->id;
                $invoice_inclusion->name = 'discount';
                $invoice_inclusion->amount = $this->discountAmt;
                $invoice_inclusion->deduct = true;
                $invoice_inclusion->save();
            }

            if ($this->allow_shipping) {
                $invoice_inclusion = new InvoiceInclusion;
                $invoice_inclusion->invoice_id = $invoice->id;
                $invoice_inclusion->name = 'shipping_fee';
                $invoice_inclusion->amount = $this->shippingAmt;
                $invoice_inclusion->deduct = false;
                $invoice_inclusion->save();
            }

            $this->alert(
                $this->recipient,
                $module_slug,
                $this->booking->id,
                $message,
            );

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('MerchantSellerCenterServicesBookingsQuotationCreate.submit: ' . $ex->getMessage());

            $this->apiErrorMsg = 'Something went wrong. Please try again later.';
            return;
        }

        session()->flash('success', 'Invoice issued to ' . $this->recipient->name);
        return $this->redirect(route('merchant.seller-center.services.show.bookings.details', ['merchant' => $this->merchant, 'service' => $this->service, 'type' => 'bookings', 'booking' => $this->booking]));
    }

    #[Layout('layouts.merchant.seller-center')]
    public function render()
    {
        return view('merchant.seller-center.services.bookings.merchant-seller-center-services-bookings-quotation-create');
    }
}
