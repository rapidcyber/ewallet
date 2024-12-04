<?php

namespace App\User\Orders\Modals;

use App\Models\OrderCancel;
use App\Models\OrderCancelReason;
use App\Models\ProductOrder;
use App\Models\ProductOrderLog;
use App\Models\ShippingStatus;
use App\Models\User;
use App\Traits\WithImageUploading;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class UserCancelOrderModal extends Component
{
    use WithImageUploading;

    public ProductOrder $order;
    public $visible = true;

    public $reason;
    public $comment;
    public $uploaded_images = [];

    #[Locked]
    public $button_clickable = true;

    public function mount($order_id)
    {
        $accepted_status = ShippingStatus::whereIn('slug', ['unpaid', 'pending', 'packed', 'ready_to_ship'])->pluck('id')->toArray();

        $this->order = ProductOrder::where('id', $order_id)
            ->whereHasMorph('buyer', User::class, function ($q) {
                $q->where('id', auth()->id());
            })
            ->whereIn('shipping_status_id', $accepted_status)
            ->firstOrFail();
    }

    #[On('updateImages')]
    public function updateImages($images)
    {
        $this->uploaded_images = $images;

        foreach ($this->uploaded_images as $key => $image) {
            $this->uploaded_images[$key]['image'] = new TemporaryUploadedFile($image['image'], config('filesystems.default'));
        }
    }

    #[Computed(persist: true)]
    public function get_cancel_reasons()
    {
        return OrderCancelReason::where('entity', 'buyer')
            ->orderBy('name')
            ->toBase()
            ->get();
    }

    public function submit()
    {
        $this->validate([
            'reason' => 'required|in:' . implode(',', array_column($this->get_cancel_reasons->toArray(), 'slug')),
            'comment' => 'required|string|max:255',
            'uploaded_images' => 'array|max:5',
            'uploaded_images.*' => 'array:id,name,image,size,order',
            'uploaded_images.*.id' => 'nullable',
            'uploaded_images.*.name' => 'required',
            'uploaded_images.*.image' => 'required|image|mimes:png,jpg,jpeg|max:5120',
            'uploaded_images.*.size' => 'required',
            'uploaded_images.*.order' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $status_cancellation = ShippingStatus::where('slug', 'cancellation')->firstOrFail();

            $reason = $this->get_cancel_reasons->where('slug', $this->reason)->first();
    
            $this->order->termination_reason = $reason->name;
            $this->order->shipping_status_id = $status_cancellation->id;
            $this->order->save();

            $order_cancel = new OrderCancel;
            $order_cancel->product_order_id = $this->order->id;
            $order_cancel->cancelled_by = 'buyer';
            $order_cancel->order_cancel_reason_id = $reason->id;
            $order_cancel->comment = $this->comment;
            $order_cancel->save();

            foreach ($this->uploaded_images as $image) {
                $this->upload_file_media($order_cancel, $image['image'], 'order_cancel_images');
            }

            $log = new ProductOrderLog;
            $log->product_order_id = $this->order->id;
            $log->shipping_status_id = $status_cancellation->id;
            $log->title = 'Cancelled by Buyer';
            $log->description = 'Product order has been cancelled by the buyer. Reason: ' . $reason->name;
            $log->save();

            DB::commit();

            $this->button_clickable = false;
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('UserCancelOrderModal.submit:' . $ex);
            $this->dispatch('errorModal', [
                'header' => 'Error',
                'message' => 'Error while cancelling order. Please try again.',
            ]);
            return;
        }


        $this->dispatch('successSubmit', [
            'header' => 'Success',
            'message' => 'Function to be added soon.',
        ]);
    }
    
    public function render()
    {
        return view('user.orders.modals.user-cancel-order-modal');
    }
}
