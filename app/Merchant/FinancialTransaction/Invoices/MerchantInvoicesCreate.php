<?php

namespace App\Merchant\FinancialTransaction\Invoices;

use App\Models\Balance;
use App\Models\Invoice;
use App\Models\InvoiceInclusion;
use App\Models\InvoiceItem;
use App\Models\Merchant;
use App\Models\User;
use App\Traits\Traits\WithStringManipulation;
use App\Traits\WithImage;
use App\Traits\WithNotification;
use App\Traits\WithNumberGeneration;
use App\Traits\WithValidPhoneNumber;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;

class MerchantInvoicesCreate extends Component
{
    use WithNumberGeneration, WithImage, WithValidPhoneNumber, WithStringManipulation, WithNotification;

    public Merchant $merchant;

    #[Locked]
    public $recipient = null;

    #[Locked]
    public $results = [];

    public $isAddItemVisible = false;
    public $isEditItemModalVisible = false;
    public $isAddInclusionVisible = false;

    #[Locked]
    public $is_admin = false;

    /**
     * Placeholder
     */
    public $new_item = [
        'name' => '',
        'description' => '',
        'quantity' => 0,
        'price' => 0,
    ];

    public $edit_key;
    public $edit_item = [
        'name' => '',
        'description' => '',
        'quantity' => 0,
        'price' => 0,
    ];

    public $recipient_type = 'user';
    public $phone_iso = '+63';
    public $phone_number;
    public $currency = 'PHP';
    #[Validate('date|date_format:Y-m-d|after_or_equal:today')]
    public $due_date;
    // items and inclusions
    public $items = [];
    public $allow_vat = false;
    public $allow_discount = false;
    public $allow_shipping = false;
    public $allow_partial = false;
    public $discountAmt;
    public $shippingAmt;
    public $partialAmt;
    public $agree = false;
    public $apiErrorMsg = '';
    public $apiSuccessMsg = '';

    protected $allowedCurrencyValues = [
        'PHP'
    ];

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;
    }

    #[Computed]
    public function balance_amount()
    {
        return $this->merchant->latest_balance()->first()->amount ?? 0;
    }

    #[Computed(persist: true)]
    public function get_phone_isos()
    {
        return config('constants.phone_number_prefixes');
    }

    public function updatedPhoneNumber()
    {
        if (empty($this->phone_number)) {
            $this->results = [];
            return;
        }

        if (!$this->phone_iso || !in_array($this->phone_iso, array_column($this->get_phone_isos, 'dial_code'))) {
            $this->results = [];
            return;
        }

        $phone_number = str_replace('+', '', $this->phone_iso) . $this->phone_number;

        if (!empty($this->recipient) && $this->recipient['phone_number'] !== $phone_number) {
            $this->reset(['recipient']);
        }

        $results = [];

        $users = User::active()
            ->where('phone_number', $phone_number)
            ->with([
                'profile' => function ($q) {
                    $q->orderBy('first_name', 'asc')
                        ->select('id', 'user_id', 'first_name', 'surname');
                }
            ])
            ->select('id', 'phone_number')
            ->limit(5)
            ->get();

        foreach ($users as $user) {
            $results[] = [
                'type' => 'user',
                'name' => $this->mask_name($user->name),
                'phone_number' => $user->phone_number,
            ];
        }

        usort($results, function ($a, $b) {
            return $a['name'] <=> $b['name'];
        });

        $this->results = $results;
    }

    public function handleRecipientSelection($phone_number)
    {
        $recipient = User::where('phone_number', $phone_number)
            ->with([
                'profile' => function ($q) {
                    $q->select('id', 'user_id', 'first_name', 'surname', 'status');
                },
                'media' => function ($q) {
                    $q->where('collection_name', 'profile_picture');
                }
            ])
            ->first();

        if (empty($recipient)) {
            return;
        }

        $this->recipient = [
            'name' => $this->mask_name($recipient->name),
            'phone_number' => $this->format_phone_number($recipient->phone_number, $recipient->phone_iso),
        ];
        $this->results = [];
    }

    public function handleAddItemModal($boolVal)
    {
        if ($boolVal) {
            $this->new_item = [
                'name' => '',
                'description' => '',
                'price' => 0,
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
            'recipient_type' => 'required|in:user,merchant',
            'phone_iso' => 'nullable|required_if:recipient_type,user|in:' . implode(',', array_column($this->get_phone_isos, 'dial_code')),
            'phone_number' => [
                'nullable',
                'required_if:recipient_type,user',
                'numeric',
                function ($attribute, $value, $fail) {
                    $phone_number = str_replace('+', '', $this->phone_iso) . $value;
                    if (!User::where('phone_number', $phone_number)->exists()) {
                        $fail('Invalid phone number');
                    }
                }
            ],
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
            'discountAmt' => 'nullable|required_if:allow_discount,1|numeric|min:1|max:' . $this->total,
            'shippingAmt' => 'nullable|required_if:allow_shipping,1|numeric|min:1|max:' . $this->total,
            'partialAmt' => 'nullable|required_if:allow_partial,1|numeric|min:1|max:' . $this->total,
            'agree' => 'accepted'
        ]);

        if ($this->recipient_type == 'user') {
            $phone_number = str_replace('+', '', $this->phone_iso) . $this->phone_number;
            $recipient = User::where('phone_number', $phone_number)->first();
        }


        $invoice = new Invoice;
        $invoice->sender_id = $this->merchant->id;
        $invoice->sender_type = Merchant::class;
        $invoice->recipient_id = $recipient->id;
        $invoice->recipient_type = get_class($recipient);
        $invoice->invoice_no = $this->generate_invoice_number($this->merchant, $recipient);
        $invoice->currency = $this->currency;
        $invoice->message = '';
        $invoice->due_date = $this->due_date;
        $invoice->status = 'unpaid';
        $invoice->minimum_partial = $this->allow_partial ? $this->partialAmt : null;
        $invoice->type = 'payable';

        DB::beginTransaction();
        try {
            $invoice->save();

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
                $recipient,
                'invoice',
                $invoice->invoice_no,
                "You have received an invoice from {$this->merchant->name}.\n\nInvoice number {$invoice->invoice_no}"
            );

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('MerchantInvoicesCreate.submit: ' . $th->getMessage());

            return $this->apiErrorMsg = 'Something went wrong. Please try again later.';
        }

        $this->apiSuccessMsg = "Invoice issued to {$this->format_phone_number($recipient->phone_number, $recipient->phone_iso)}. Invoice number: {$invoice->invoice_no}";
        $this->clearValidation();
        $this->reset([
            'recipient',
            'currency',
            'due_date',
            'items',
            'allow_vat',
            'allow_discount',
            'allow_shipping',
            'allow_partial',
            'discountAmt',
            'shippingAmt',
            'partialAmt',
            'agree'
        ]);
    }

    #[Layout('layouts.merchant.financial-transaction')]
    public function render()
    {
        return view('merchant.financial-transaction.invoices.merchant-invoices-create');
    }
}
