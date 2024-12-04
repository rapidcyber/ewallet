<?php

namespace App\Admin\Disputes\ReturnOrders\Modals;

use App\Models\AdminLog;
use App\Models\Merchant;
use App\Models\ReturnCancel;
use App\Models\ReturnCancelReason;
use App\Models\ReturnOrder;
use App\Models\ReturnOrderDisputeDecision;
use App\Models\ReturnOrderLog;
use App\Models\ReturnOrderStatus;
use App\Models\User;
use App\Traits\WithImage;
use App\Traits\WithImageUploading;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class AdminReturnOrdersCancelModal extends Component
{
    use WithImage, WithImageUploading;
    
    public Merchant $merchant;
    public ReturnOrder $return_order;
    public $visible = true;
    public $reason;
    public $comment;
    public $uploaded_images = [];

    #[Locked]
    public $button_clickable = true;

    public function mount(Merchant $merchant, $return_order_id)
    {
        $this->merchant = $merchant;

        $allowed_status = ReturnOrderStatus::where('slug', 'pending_resolution')
            ->whereHas('parent_status', function ($q) {
                $q->where('slug', 'dispute_in_progress');
            })
            ->firstOrFail();

        $this->return_order = $merchant->return_orders_through_products()
            ->where('return_orders.id', $return_order_id)
            ->where('return_orders.return_order_status_id', $allowed_status->id)
            ->whereHas('dispute', function ($q) {
                $q->whereDoesntHave('decision');
            })
            ->with([
                'product_order.buyer' => function (MorphTo $q) {
                    $q->morphWith([
                        User::class => ['profile', 'media' => function ($q) {
                            $q->where('collection_name', 'profile_picture');
                        }],
                        Merchant::class => ['media' => function ($q) {
                            $q->where('collection_name', 'merchant_logo');
                        }]
                    ]);
                },
                'product_order.product.first_image',
                'reason',
            ])
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

    #[Computed]
    public function get_cancel_reasons()
    {
        return ReturnCancelReason::orderBy('name')->toBase()->get();
    }

    public function cancel()
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

        $dispute = $this->return_order->dispute;

        $dispute_decision = new ReturnOrderDisputeDecision;
        $dispute_decision->return_order_dispute_id = $dispute->id;
        $dispute_decision->type = 'cancel';

        $admin_log = new AdminLog;
        $admin_log->user_id = auth()->id();
        $admin_log->title = 'Canceled return order ' . $this->return_order->id . ' for merchant ' . $this->merchant->id;

        $status_refunded_only = ReturnOrderStatus::where('slug', 'return_cancelled')->firstOrFail();
        $this->return_order->return_order_status_id = $status_refunded_only->id;
        
        $return_log = new ReturnOrderLog;
        $return_log->return_order_id = $this->return_order->id;
        $return_log->return_order_status_id = $status_refunded_only->id;
        $return_log->title = 'Admin resolved dispute - Cancel';
        $return_log->description = 'Admin has resolved the dispute. Return order request is canceled.';
        
        DB::beginTransaction();
        try {
            $dispute_decision->save();
            $admin_log->save();
            $this->return_order->save();
            $return_log->save();

            $reason = $this->get_cancel_reasons->where('slug', $this->reason)->firstOrFail();

            $return_cancel = new ReturnCancel;
            $return_cancel->return_order_id = $this->return_order->id;
            $return_cancel->return_cancel_reason_id = $reason->id;
            $return_cancel->comment = $this->comment;
            $return_cancel->save();

            foreach ($this->uploaded_images as $image) {
                $this->upload_file_media($return_cancel, $image['image'], 'return_cancel_images');
            }

            DB::commit();
            $this->button_clickable = false;
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('AdminReturnOrdersCancelModal.cancel - ' . $ex->getMessage());
            session()->flash('error', 'Failed to process cancelation');
            session()->flash('error_message', 'Please try again later');
            $this->button_clickable = true;
            return;
        }

        $this->dispatch('successModal', [
            'header' => 'Success',
            'message' => 'Cancelled the return order',
        ]);
    }

    public function render()
    {
        return view('admin.disputes.return-orders.modals.admin-return-orders-cancel-modal');
    }
}
