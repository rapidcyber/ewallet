<?php

namespace App\Merchant\SellerCenter\Logistics\Orders;

use App\Models\Merchant;
use App\Models\ProductOrderDocument;
use App\Models\ProductOrderLog;
use App\Models\ShippingStatus;
use App\Models\User;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithImage;
use App\Traits\WithNotification;
use App\Traits\WithValidPhoneNumber;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class MerchantSellerCenterLogisticsOrders extends Component
{
    use WithPagination, WithCustomPaginationLinks, WithImage, WithValidPhoneNumber, WithNotification;

    public Merchant $merchant;
    #[Url(as: 'status', keep: false)]
    public $activeBox = null;
    #[Url(as: 'to_ship', keep: false)]
    public $to_ship_status = 'pending';
    public $searchTerm = '';
    #[Locked]
    public $search_value = '';
    public $date;
    public $deadline;
    public $amount;
    public $delivery_type;
    public $order_id = null;
    public $show_modal = false;
    public $show_cancel_order_modal = false;

    #[Locked]
    public $product_order = null;
    #[Locked]
    public $order_logs = null;
    #[Locked]
    public $delivery_status = 0;

    protected $allowed_date_options = [
        'today',
        'past_week',
        'past_month',
        'past_6_months',
        'past_year',
    ];

    protected $allowed_deadline_options = [
        '12',
        '24',
        '48',
        '72',
    ];

    protected $allowed_amount_options = [
        '0-4999',
        '5000-9999',
        '10000-14999',
        '15000+'
    ];

    protected $allowed_delivery_type_options = [
        'standard',
        'on_demand',
    ];

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;
    }

    #[Computed]
    public function shipping_statuses()
    {
        return ShippingStatus::all();
    }

    public function updatedActiveBox()
    {
        if (!in_array($this->activeBox, ['unpaid', 'to_ship', 'shipping', 'completed', 'cancellation', 'failed_delivery'])) {
            $this->activeBox = null;
        }

        $this->resetPage();
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function updatedToShipStatus()
    {
        if (!in_array($this->to_ship_status, ['pending', 'packed', 'ready_to_ship'])) {
            $this->to_ship_status = 'pending';
        }

        $this->resetPage();
    }

    public function updatedShowModal()
    {
        if ($this->show_modal == false) {
            $this->reset(['order_id', 'order_logs', 'delivery_status']);
        }
    }

    private function get_statuses()
    {
        switch ($this->activeBox) {
            case 'unpaid':
                return $this->shipping_statuses->where('slug', 'unpaid')->first()->id;
            case 'to_ship':
                if ($this->to_ship_status === 'pending') {
                    return $this->shipping_statuses->where('slug', 'pending')->first()->id;
                } elseif ($this->to_ship_status === 'packed') {
                    return $this->shipping_statuses->where('slug', 'packed')->first()->id;
                } elseif ($this->to_ship_status === 'ready_to_ship') {
                    return $this->shipping_statuses->where('slug', 'ready_to_ship')->first()->id;
                }
                break;
            case 'shipping':
                return $this->shipping_statuses->where('slug', 'shipping')->first()->id;
            case 'completed':
                return $this->shipping_statuses->where('slug', 'completed')->first()->id;
            case 'cancellation':
                return $this->shipping_statuses->where('slug', 'cancellation')->first()->id;
            case 'failed_delivery':
                return $this->shipping_statuses->where('slug', 'failed_delivery')->first()->id;
            default:
                return [];
        }
    }

    #[Computed]
    public function count_all()
    {
        return $this->merchant->orders_through_products()->count();
    }

    #[Computed]
    public function count_unpaid()
    {
        $status_unpaid = $this->shipping_statuses->where('slug', 'unpaid')->first()->id;
        return $this->merchant->orders_through_products()->where('shipping_status_id', $status_unpaid)->count();
    }

    #[Computed]
    public function count_to_ship()
    {
        $status_to_ship = $this->shipping_statuses->whereIn('slug', ['pending', 'packed', 'ready_to_ship'])->pluck('id')->toArray();
        return $this->merchant->orders_through_products()->whereIn('shipping_status_id', $status_to_ship)->count();
    }

    #[Computed]
    public function count_shipping()
    {
        $status_shipping = $this->shipping_statuses->where('slug', 'shipping')->first()->id;
        return $this->merchant->orders_through_products()->where('shipping_status_id', $status_shipping)->count();
    }

    #[Computed]
    public function count_completed()
    {
        $status_completed = $this->shipping_statuses->where('slug', 'completed')->first()->id;
        return $this->merchant->orders_through_products()->where('shipping_status_id', $status_completed)->count();
    }

    #[Computed]
    public function count_cancelled()
    {
        $status_cancelled = $this->shipping_statuses->where('slug', 'cancellation')->first()->id;
        return $this->merchant->orders_through_products()->where('shipping_status_id', $status_cancelled)->count();
    }

    #[Computed]
    public function count_failed()
    {
        $status_failed = $this->shipping_statuses->where('slug', 'failed_delivery')->first()->id;
        return $this->merchant->orders_through_products()->where('shipping_status_id', $status_failed)->count();
    }

    #[Computed]
    public function cancellation_rate()
    {
        $now = Carbon::now();
        $past_date = $now->copy()->subDays(28)->startOfDay();

        $status_cancelled = $this->shipping_statuses->where('slug', 'cancellation')->first()->id;
        $count_cancelled = $this->merchant->orders_through_products()
            ->where('shipping_status_id', $status_cancelled)
            ->whereBetween('product_orders.created_at', [$past_date, $now->endOfDay()])
            ->count();

        $count_orders = $this->merchant->orders_through_products()
            ->whereBetween('product_orders.created_at', [$past_date, $now->endOfDay()])
            ->count();

        if ($count_orders == 0) {
            return 0;
        }

        return ($count_cancelled / $count_orders) * 100;
    }

    #[Computed]
    public function late_fulfillment_rate()
    {
        $now = Carbon::now();
        $past_date = $now->copy()->subDays(28)->startOfDay();

        $pending_packed_status = $this->shipping_statuses->whereIn('slug', ['pending', 'packed'])->pluck('id')->toArray();

        $count_pending_packed_expired = $this->merchant->orders_through_products()
            ->whereIn('shipping_status_id', $pending_packed_status)
            ->whereBetween('product_orders.created_at', [$past_date, $now->endOfDay()])
            ->whereRaw('DATE_ADD(product_orders.created_at, INTERVAL 96.5 HOUR) < NOW()')
            ->count();

        $count_orders = $this->merchant->orders_through_products()
            ->whereBetween('product_orders.created_at', [$past_date, $now->endOfDay()])
            ->count();

        if ($count_orders == 0) {
            return 0;
        }

        return ($count_pending_packed_expired / $count_orders) * 100;
    }

    #[Computed]
    public function fast_fulfillment_rate()
    {
        $now = Carbon::now();
        $past_date = $now->copy()->subDays(28)->startOfDay();

        $ready_to_ship_status = $this->shipping_statuses->where('slug', 'ready_to_ship')->first()->id;

        $count_ready_to_ship = $this->merchant->orders_through_products()
            ->where('shipping_status_id', $ready_to_ship_status)
            ->whereBetween('product_orders.created_at', [$past_date, $now->endOfDay()])
            ->whereRaw('DATE_ADD(product_orders.created_at, INTERVAL 72.5 HOUR) > NOW()')
            ->count();

        $count_orders = $this->merchant->orders_through_products()
            ->whereBetween('product_orders.created_at', [$past_date, $now->endOfDay()])
            ->count();

        if ($count_orders == 0) {
            return 0;
        }

        return ($count_ready_to_ship / $count_orders) * 100;
    }

    public function calculate_remaining_hours($created_at)
    {
        $date = Carbon::parse($created_at);
        $target_time = $date->copy()->addHours(96.5);

        if ($target_time->lt(Carbon::now())) {
            return 'Expired';
        }

        return (int)$target_time->diffInHours(null, true) . ' hours left';
    }

    public function pack_and_print()
    {
        $product_order = $this->merchant->orders_through_products()
            ->where('product_orders.id', $this->order_id)
            ->with('buyer')
            ->first();

        if (!$product_order) {
            return session()->flash('error', 'Product order not found');
        }

        $status_pending = $this->shipping_statuses->where('slug', 'pending')->first()->id;
        
        if ($product_order->shipping_status_id != $status_pending) {
            return session()->flash('error', 'Invalid product order');
        }
        
        DB::beginTransaction();
        try {
            $product_order->shipping_status_id = $this->shipping_statuses->where('slug', 'packed')->first()->id;
            $product_order->save();

            $log = new ProductOrderLog;
            $log->product_order_id = $product_order->id;
            $log->shipping_status_id = $this->shipping_statuses->where('slug', 'packed')->first()->id;
            $log->title = 'Packed by Seller';
            $log->description = 'Product order has been marked as packed by seller';
            $log->save();
            
            $this->alert(
                $product_order->buyer, 
                'notification', 
                $product_order->order_number,
                "#{$product_order->order_number} - Your product order has been marked as packed by the seller.",
              
            );

            session()->flash('success', 'Marked as Packed');
            session()->flash('success_message', 'Product order has been marked as packed successfully');
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('MerchantSellerCenterLogisticsOrders.pack_and_print: ' . $th->getMessage());
            session()->flash('error', 'An error has occurred. Please try again later.');
        }
        
        return $this->reset(['show_modal', 'order_id']);
    }

    public function arrange_shipment()
    {
        $product_order = $this->merchant->orders_through_products()
            ->where('product_orders.id', $this->order_id)
            ->with('buyer')
            ->first();

        if (!$product_order) {
            return session()->flash('error', 'Product order not found');
        }

        $status_packed = $this->shipping_statuses->where('slug', 'packed')->first()->id;

        if ($product_order->shipping_status_id != $status_packed) {
            return session()->flash('error', 'Invalid product order');
        }

        DB::beginTransaction();
        try {
            $product_order->shipping_status_id = $this->shipping_statuses->where('slug', 'ready_to_ship')->first()->id;
            $product_order->save();

            $log = new ProductOrderLog;
            $log->product_order_id = $product_order->id;
            $log->shipping_status_id = $this->shipping_statuses->where('slug', 'ready_to_ship')->first()->id;
            $log->title = 'Shipment to be arranged';
            $log->description = 'Seller has arranged shipment for the product';
            $log->save();

            $this->alert(
                $product_order->buyer, 
                'notification', 
                $product_order->order_number,
                "#{$product_order->order_number} - Your product order has been marked as ready to ship.",
                
            );
            
            session()->flash('success', 'Marked as Ready to Ship');
            session()->flash('success_message', 'Product order has been marked as ready to ship successfully');
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('MerchantSellerCenterLogisticsOrders.arrange_shipment: ' . $th->getMessage());
            session()->flash('error', 'An error has occurred. Please try again later.');
        }

        $this->reset(['show_modal', 'order_id']);
    }

    public function cancel_order()
    {
        $product_order = $this->merchant->orders_through_products()
            ->where('product_orders.id', $this->order_id)
            ->with('buyer')
            ->first();

        if (!$product_order) {
            return session()->flash('error', 'Product order not found');
        }

        $accepted_statuses = $this->shipping_statuses->whereIn('slug', ['unpaid', 'pending', 'packed', 'ready_to_ship'])->pluck('id')->toArray();
        if (!in_array($product_order->shipping_status_id, $accepted_statuses)) {
            return session()->flash('error', 'Product order cannot be cancelled');
        }

        DB::beginTransaction();
        try {
            $product_order->shipping_status_id = $this->shipping_statuses->where('slug', 'cancellation')->first()->id;
            $product_order->cancelled_by = 'seller';
            $product_order->save();

            $log = new ProductOrderLog;
            $log->product_order_id = $product_order->id;
            $log->shipping_status_id = $this->shipping_statuses->where('slug', 'cancellation')->first()->id;
            $log->title = 'Cancelled by Seller';
            $log->description = 'Product order has been cancelled by the seller';
            $log->save();

            $this->alert(
                $product_order->buyer, 
                'notification', 
                $product_order->order_number,
                "#{$product_order->order_number} - Your product order has been cancelled by the seller.",
                
            );
            
            DB::commit();
            return session()->flash('success', 'Product order cancelled successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('MerchantSellerCenterLogisticsOrders.cancel_order: ' . $th->getMessage());
            session()->flash('error', 'An error has occurred. Please try again later.');
        }

        $this->reset(['show_modal', 'order_id']);
    }

    public function open_cancel_order_modal($order_id)
    {
        $accepted_statuses = $this->shipping_statuses->whereIn('slug', ['unpaid', 'pending', 'packed', 'ready_to_ship'])->pluck('id')->toArray();

        $product_order = $this->merchant->orders_through_products()
            ->where('product_orders.id', $order_id)
            ->whereIn('product_orders.shipping_status_id', $accepted_statuses)
            ->first();

        if (!$product_order) {
            return session()->flash('error', 'Product order not found');
        }

        $this->show_cancel_order_modal = true;
        $this->order_id = $order_id;
    }

    public function show_order_logs($order_id)
    {
        $product_order = $this->merchant->orders_through_products()
            ->where('product_orders.id', $order_id)
            ->with(['logs', 'shipping_status'])
            ->first();

        if (!$product_order) {
            return session()->flash('error', 'Product order not found');
        }

        $not_allowed_statuses = $this->shipping_statuses->whereIn('slug', ['unpaid', 'pending', 'packed'])->pluck('id')->toArray();

        if (in_array($product_order->shipping_status_id, $not_allowed_statuses)) {
            return session()->flash('error', 'Product order does not have logs yet');
        }

        switch ($product_order->shipping_status->name) {
            case 'Unpaid':
            case 'Pending':
            case 'Packed':
            case 'Cancellation':
            case 'Failed Delivery':
                $this->delivery_status = 0;
                break;
            case 'Ready to Ship':
                $this->delivery_status = 1;
                break;
            case 'Shipping':
                $this->delivery_status = 2;
                break;
            case 'Completed':
                $this->delivery_status = 3;
                break;
        }

        $this->product_order = $product_order->load('shipping_option');
        $this->order_logs = $product_order->logs;
        $this->show_modal = true;
    }

    #[On('successModal')]
    public function successModal($message)
    {
        $this->closeModal();
        if (isset($message['header'])) {
            session()->flash('success', $message['header']);
        }

        if (isset($message['message'])) {
            session()->flash('success_message', $message['message']);
        }
    }

    #[On('failedModal')]
    public function failedModal($message)
    {
        $this->closeModal();
        if (isset($message['header'])) {
            session()->flash('error', $message['header']);
        }

        if (isset($message['message'])) {
            session()->flash('error_message', $message['message']);
        }
    }

    #[On('closeModal')]
    public function closeModal()
    {
        $this->show_modal = false;
        $this->updatedShowModal();
    }

    public function search()
    {
        $this->search_value = $this->searchTerm;
    }

    public function reset_search()
    {
        $this->reset(['searchTerm', 'search_value']);
    }

    public function view_product_order($order_id)
    {
        $product_order = $this->merchant->orders_through_products()
            ->where('product_orders.id', $order_id)
            ->first();

        if (!$product_order) {
            return session()->flash('error', 'Product order not found');
        }

        return $this->redirect(route('merchant.seller-center.logistics.orders.show', ['merchant' => $this->merchant, 'productOrder' => $product_order]));
    }

    public function download_documents($product_order_id, $document)
    {
        $product_order = $this->merchant->orders_through_products()
            ->where('product_orders.id', $product_order_id)
            ->with('documents')
            ->first();

        if (!$product_order) {
            return session()->flash('error', 'Product Order not found');
        }

        if (!in_array($document, ['awb', 'pick-list', 'packing-list', 'all'])) {
            return session()->flash('error', 'Invalid document');
        }

        if ($product_order->documents) {
            $documents_list = $product_order->documents;
        } else {
            $documents_list = new ProductOrderDocument;
            $documents_list->product_order_id = $product_order->id;
        }

        switch ($document) {
            case 'awb':
                session()->flash('warning', 'AWB document file not available yet.');
                session()->flash('warning_message', 'You may proceed for now.');
                $documents_list->awb_downloaded = true;
                break;
            case 'pick-list':
                session()->flash('warning', 'Pick List document file not available yet.');
                session()->flash('warning_message', 'You may proceed for now.');
                $documents_list->pick_list_downloaded = true;
                break;
            case 'packing-list':
                session()->flash('warning', 'Packing List document file not available yet.');
                session()->flash('warning_message', 'You may proceed for now.');
                $documents_list->packing_list_downloaded = true;
                break;
            case 'all':
                session()->flash('warning', 'All document files not available yet.');
                session()->flash('warning_message', 'You may proceed for now.');
                $documents_list->awb_downloaded = true;
                $documents_list->pick_list_downloaded = true;
                $documents_list->packing_list_downloaded = true;
                break;
        }

        $documents_list->save();
    }

    private function get_date_from()
    {
        $now = Carbon::now();
        switch ($this->date) {
            case 'today':
                return $now->copy()->startOfDay();
            case 'past_week':
                return $now->copy()->subDays(7)->startOfDay();
            case 'past_month':
                return $now->copy()->subMonth()->startOfDay();
            case 'past_6_months':
                return $now->copy()->subMonths(6)->startOfDay();
            case 'past_year':
                return $now->copy()->subYear()->startOfDay();
            default:
                return null;
        }
    }

    private function get_amount_range()
    {
        switch ($this->amount) {
            case '0-4999':
                return [0, 4999];
            case '5000-9999':
                return [5000, 9999];
            case '10000-14999':
                return [10000, 14999];
            case '15000+':
                return [15000];
            default:
                return null;
        }
    }

    #[Layout('layouts.merchant.seller-center')]
    public function render()
    {
        $product_orders = $this->merchant->orders_through_products()
            ->with([
                'shipping_status',
                'shipping_option',
                'payment_option',
                'product.first_image',
                'documents',
                'warehouse',
                'buyer' => function (MorphTo $query) {
                    $query->morphWith([
                        User::class => ['profile', 'media' => function ($query) {
                            $query->where('collection_name', 'profile_picture');
                        }],
                        Merchant::class => ['media' => function ($query) {
                            $query->where('collection_name', 'merchant_logo');
                        }],
                    ]);
                }
            ])
            ->select('product_orders.*');

        if ($this->activeBox) {
            $status = $this->get_statuses();
            $product_orders = $product_orders->where('shipping_status_id', $status);
        }

        if ($this->date and in_array($this->date, $this->allowed_date_options)) {
            $date_from = $this->get_date_from();
            $product_orders = $product_orders->whereBetween('product_orders.created_at', [$date_from, Carbon::now()]);
        }

        if ($this->deadline and in_array($this->deadline, $this->allowed_deadline_options)) {
            $product_orders = $product_orders->whereRaw('DATE_ADD(product_orders.created_at, INTERVAL 96.5 HOUR) < DATE_ADD(UTC_TIMESTAMP(), INTERVAL ' . $this->deadline . ' HOUR)'); 
        }

        if ($this->amount and in_array($this->amount, $this->allowed_amount_options)) {
            $amount_range = $this->get_amount_range();
            if (count($amount_range) == 2) {
                $product_orders = $product_orders->whereRaw("(product_orders.amount * product_orders.quantity) BETWEEN {$amount_range[0]} AND {$amount_range[1]}");
            } elseif (count($amount_range) == 1) {
                $product_orders = $product_orders->whereRaw("(product_orders.amount * product_orders.quantity) > {$amount_range[0]}");
            }
        }

        if ($this->delivery_type and in_array($this->delivery_type, $this->allowed_delivery_type_options)) {
            $product_orders = $product_orders->where('delivery_type', $this->delivery_type);
        }

        if ($this->search_value) {
            $product_orders = $product_orders->where(function ($query) {
                $query->whereHas('product', function ($q) {
                    $q->where('name', 'like', "%{$this->search_value}%");
                });
                $query->orWhereHasMorph('buyer', User::class, function ($q) {
                    $q->whereHas('profile', function ($q) {
                        $q->where('first_name', 'like', "%{$this->search_value}%");
                        $q->orWhere('surname', 'like', "%{$this->search_value}%");
                    });
                });
                $query->orWhereHasMorph('buyer', Merchant::class, function ($q) {
                    $q->where('name', 'like', "%{$this->search_value}%");
                });
                $query->orWhere('tracking_number', 'like', "%{$this->search_value}%");
                $query->orWhere('order_number', 'like', "%{$this->search_value}%");
            });
        }

        $product_orders = $product_orders->orderBy('created_at', 'desc')->paginate(8);
        $elements = $this->getPaginationElements($product_orders);

        return view('merchant.seller-center.logistics.orders.merchant-seller-center-logistics-orders-list')->with([
            'product_orders' => $product_orders,
            'elements' => $elements,
        ]);
    }
}
