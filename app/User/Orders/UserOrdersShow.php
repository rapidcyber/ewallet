<?php

namespace App\User\Orders;

use App\Models\ProductOrder;
use App\Models\ProductOrderLog;
use App\Models\ProductReview;
use App\Models\ShippingStatus;
use App\Models\User;
use App\Traits\WithImage;
use App\Traits\WithImageUploading;
use App\Traits\WithValidPhoneNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class UserOrdersShow extends Component
{
    use WithImage, WithValidPhoneNumber;

    public User $user;
    public ProductOrder $product_order;

    #[Locked]
    public $show_review_modal = false;
    #[Locked]
    public $show_return_modal = false;
    public $show_cancel_modal = false;

    #[Locked]
    public $delivery_status = 0;

    #[Locked]
    public $button_clickable = true;
    public $order_cancel_id = null;

    public function mount(ProductOrder $productOrder)
    {
        $this->user = auth()->user();

        $this->product_order = $this->user->product_orders()
            ->where('product_orders.id', $productOrder->id)
            ->with([
                'shipping_status.parent_status',
                'product' => fn($q) => $q->withTrashed(),
                'product.merchant.logo',
                'product.first_image',
                'buyer',
                'payment_option',
                'location',
                'logs.shipping_status',
                'shipping_option',
                'cancellation.reason',
                'cancellation.media',
            ])
            ->firstOrFail();

        switch ($this->product_order->shipping_status->name) {
            case 'To Ship':
            case 'Pending':
            case 'Packed':
            case 'Ready to Ship':
                $this->delivery_status = 1;
                break;
            case 'Shipping':
                $this->delivery_status = 2;
                break;
            case 'Completed':
                $this->delivery_status = 3;
                break;
            default:
                $this->delivery_status = 0;
                break;
        }
    }

    #[Computed]
    public function shipping_status()
    {
        return $this->product_order->shipping_status()->firstOrFail();
    }

    #[Computed]
    public function check_review_exists()
    {
        return $this->user->product_reviews()->where('product_reviews.product_id', $this->product_order->product_id)->exists();
    }

    #[Computed]
    public function check_return_exists()
    {
        return $this->product_order->return_orders()->exists();
    }

    public function open_review_modal()
    {
        if ($this->product_order->shipping_status->slug !== 'completed') {
            session()->flash('error', 'Product order must be completed before reviewing.');
            return;
        }

        if ($this->check_review_exists) {
            session()->flash('warning', 'Warning: Product reviewed');
            session()->flash('warning_message', 'You have already placed a review for this product.');
            return;
        }

        $this->show_review_modal = true;
    }

    public function open_return_modal()
    {
        if ($this->product_order->shipping_status->slug !== 'completed') {
            session()->flash('error', 'Error: Product Order Shipping Status');
            session()->flash('error_message', 'The product order must be completed before it can be returned.');
            return;
        }

        if ($this->check_return_exists) {
            session()->flash('warning', 'Warning: Return Order Exists');
            session()->flash('warning_message', 'A return order request already exists for this order.');
            return;
        }

        $this->show_return_modal = true;
    }

    public function open_cancel_order_modal()
    {
        $accepted_status = ShippingStatus::whereIn('slug', ['unpaid', 'pending', 'packed', 'ready_to_ship'])->pluck('id')->toArray();

        if (!in_array($this->product_order->shipping_status_id, $accepted_status)) {
            session()->flash('error', 'Order cannot be cancelled.');
            return;
        }

        $this->order_cancel_id = $this->product_order->id;
    }

    public function cancel_order()
    {
        DB::beginTransaction();
        try {
            if (!in_array($this->product_order->shipping_status->slug, ['unpaid', 'pending', 'packed', 'ready_to_ship'])) {
                return session()->flash('error', 'Order cannot be cancelled.');
            }

            $cancellation_status = ShippingStatus::where('slug', str('cancellation')->slug())->firstOrFail();
            $this->product_order->shipping_status_id = $cancellation_status->id;
            $this->product_order->cancelled_by = 'buyer';
            $this->product_order->save();

            $log = new ProductOrderLog;
            $log->product_order_id = $this->product_order->id;
            $log->shipping_status_id = $cancellation_status->id;
            $log->title = 'Buyer cancelled the order';
            $log->save();

            session()->flash('success', 'Order cancelled successfully.');

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('UserOrdersShow.cancel_order: ' . $th->getMessage());
            session()->flash('error', 'An error has occurred. Please try again later.');
            $this->button_clickable = true;
        }

        $this->button_clickable = false;
        $this->show_cancel_modal = false;
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

    #[On('closeModal')]
    public function closeModal()
    {
        $this->show_review_modal = false;
        $this->show_return_modal = false;
        $this->show_cancel_modal = false;
        $this->order_cancel_id = null;
    }

    #[Layout('layouts.user')]
    public function render()
    {
        return view('user.orders.user-orders-show');
    }
}
