<?php

namespace App\User\Orders;

use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\ShippingStatus;
use App\Models\User;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithImage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class UserOrders extends Component
{
    use WithPagination, WithCustomPaginationLinks, WithImage;
    public User $user;
    public $activeBox = '';
    public $searchTerm = '';

    // Modal Visible State
    public $is_write_review_modal_visible = false;
    public $is_request_return_modal_visible = false;

    public $order_cancel_id = null;

    #[Locked]
    public $order_review_id = null;

    #[Locked]
    public $order_return_id = null;

    protected $allowedSections = [
        '',
        'to-ship',
        'to-receive',
        'received',
        'cancellation'
    ];

    public function mount()
    {
        $this->user = User::find(auth()->id());
    }

    #[Computed]
    public function shipping_statuses()
    {
        return ShippingStatus::toBase()->get();
    }

    #[Computed]
    public function status_cancellation()
    {
        return ShippingStatus::where(function ($q) {
            $q->where('slug', str('cancellation')->slug('_'));
            $q->orWhere('slug', str('failed delivery')->slug('_'));
        })
            ->pluck('id')
            ->toArray();
    }

    #[Computed]
    public function count_all()
    {
        return $this->user->product_orders()->count();
    }

    #[Computed]
    public function count_to_ship()
    {
        $statuses = $this->shipping_statuses->whereIn(
            'slug',
            [
                str('unpaid')->slug(),
                str('pending')->slug(),
                str('packed')->slug(),
                str('ready to ship')->slug('_')
            ]
        )
            ->pluck('id')
            ->toArray();

        return $this->user->product_orders()
            ->whereIn('shipping_status_id', $statuses)
            ->count();
    }

    #[Computed]
    public function count_to_receive()
    {
        return $this->user->product_orders()
            ->where('shipping_status_id', $this->shipping_statuses->where('slug', str('shipping')->slug())->first()->id)
            ->count();
    }

    #[Computed]
    public function count_received()
    {
        return $this->user->product_orders()
            ->where('shipping_status_id', $this->shipping_statuses->where('slug', str('completed')->slug())->first()->id)
            ->count();
    }

    #[Computed]
    public function count_cancellation()
    {
        return $this->user->product_orders()
            ->whereIn('shipping_status_id', $this->status_cancellation)
            ->count();
    }

    public function updatedActiveBox()
    {
        if (! in_array($this->activeBox, $this->allowedSections)) {
            $this->activeBox = '';
        }

        $this->resetPage();
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    #[On('closeModal')]
    public function closeModal()
    {
        $this->reset(['order_review_id', 'order_return_id', 'order_cancel_id']);
    }

    #[On('successSubmit')]
    public function successSubmit($message)
    {
        if (isset($message['header'])) {
            session()->flash('success', $message['header']);
        }

        if (isset($message['message'])) {
            session()->flash('success_message', $message['message']);
        }

        $this->closeModal();
    }

    #[On('failedSubmit')]
    public function failedSubmit($message)
    {
        if (isset($message['header'])) {
            session()->flash('error', $message['header']);
        }

        if (isset($message['message'])) {
            session()->flash('error_message', $message['message']);
        }

        $this->closeModal();
    }

    public function open_review_modal($order_number)
    {
        $order = $this->user->product_orders()
            ->where('product_orders.order_number', $order_number)
            ->first();

        if (!$order) {
            session()->flash('error', 'Error: Product Order not found');
            session()->flash('error_message', 'The product order does not exist.');
            return;
        }

        if ($this->user->reviews()->whereHasMorph('entity', [Product::class], function ($query) use ($order) {
            $query->where('products.id', $order->product_id);
        })->exists()) {
            session()->flash('warning', 'Warning: Product reviewed');
            session()->flash('warning_message', 'You have already placed a review for this product.');
            return;
        }

        $this->order_review_id = $order->id;
    }

    public function open_return_modal($order_number)
    {
        $order = $this->user->product_orders()
            ->where('product_orders.order_number', $order_number)
            ->with('shipping_status')
            ->withCount('return_orders as count_return_orders')
            ->first();

        if (!$order) {
            session()->flash('error', 'Error: Product Order not found');
            session()->flash('error_message', 'The product order does not exist.');
            return;
        }

        if ($order->shipping_status->slug !== 'completed') {
            session()->flash('error', 'Error: Product Order Shipping Status');
            session()->flash('error_message', 'The product order must be completed before it can be returned.');
            return;
        }

        if ($order->count_return_orders > 0) {
            session()->flash('warning', 'Warning: Return Order Exists');
            session()->flash('warning_message', 'A return order request already exists for this order.');
            return;
        }

        $this->order_return_id = $order->id;
    }

    public function open_cancel_order_modal($order_number)
    {
        $accepted_status = $this->shipping_statuses->whereIn('slug', ['unpaid', 'pending', 'packed', 'ready_to_ship'])->pluck('id')->toArray();

        $order = $this->user->product_orders()
            ->where('product_orders.order_number', $order_number)
            ->first();

        if (!$order) {
            session()->flash('error', 'Error: Product Order not found.');
            return;
        }

        if (!in_array($order->shipping_status_id, $accepted_status)) {
            session()->flash('error', 'Error: Product Order Shipping Status');
            session()->flash('error_message', 'The order cannot be cancelled once it has been shipped.');
            return;
        }

        $this->order_cancel_id = $order->id;
    }

    public function open_order_show($order_number)
    {
        $product_order = $this->user->product_orders()
            ->where('product_orders.order_number', $order_number)
            ->first();

        if ($product_order) {
            return $this->redirect(route('user.orders.show', ['productOrder' => $product_order]));
        }

        return session()->flash('error', 'Error: Product Order not found');
    }

    #[Layout('layouts.user')]
    public function render()
    {
        $orders = $this->user->product_orders()
            ->with([
                'product' => fn($q) => $q->withTrashed(),
                'product.merchant.logo',
                'shipping_status.parent_status',
                'shipping_option',
                'payment_option',
                'product.first_image'
            ])
            ->withCount(['return_orders']);

        switch ($this->activeBox) {
            case 'to-ship':
                $to_ship = $this->shipping_statuses->whereIn('slug', [str('unpaid')->slug(), str('pending')->slug(), str('packed')->slug(), str('ready to ship')->slug('_')])->pluck('id')->toArray();
                $orders = $orders->whereIn('shipping_status_id', $to_ship);
                break;
            case 'to-receive':
                $to_receive = $this->shipping_statuses->where('slug', str('shipping')->slug())->first();
                $orders = $orders->where('shipping_status_id', $to_receive->id);
                break;
            case 'received':
                $received = $this->shipping_statuses->where('slug', str('completed')->slug())->first();
                $orders = $orders->where('shipping_status_id', $received->id);
                break;
            case 'cancellation':
                $orders = $orders->whereIn('shipping_status_id', $this->status_cancellation);
                break;
            default:
                break;
        }

        if ($this->searchTerm) {
            $orders = $orders->where(function ($query) {
                $query->where('order_number', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('tracking_number', 'like', '%' . $this->searchTerm . '%')
                    ->orWhereHas('product', function ($product) {
                        $product->where('name', 'like', '%' . $this->searchTerm . '%');
                    });
            });
        }

        $orders = $orders->orderBy('created_at', 'desc')->paginate(6);

        $elements = $this->getPaginationElements($orders);

        $product_ids = $orders->pluck('product_id')->unique()->toArray();

        $product_reviews = $this->user->reviews()
            ->whereHasMorph('entity', [Product::class], function ($q) use ($product_ids) {
                $q->whereIn('products.id', $product_ids);
            })
            ->pluck('entity_id')
            ->toArray();

        if (count($product_reviews) > 0) {
            foreach ($orders as $key => $order) {
                if (in_array($order->product_id, $product_reviews)) {
                    $orders[$key]->is_reviewed = true;
                }
            }
        }

        return view('user.orders.user-orders', [
            'orders' => $orders,
            'elements' => $elements
        ]);
    }
}
