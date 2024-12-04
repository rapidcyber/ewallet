<?php

namespace App\Merchant\SellerCenter\Logistics\ReturnOrders\Modals;

use App\Models\Merchant;
use App\Models\ReturnOrder;
use App\Models\ReturnOrderLog;
use App\Models\ReturnOrderStatus;
use App\Models\ReturnRejection;
use App\Models\ReturnRejectionReason;
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

class RejectRequestAfterReturnModal extends Component
{
    use WithImage, WithImageUploading;

    public Merchant $merchant;
    public ReturnOrder $return_order;

    #[Locked]
    public $button_clickable = true;

    public $reason = '';
    public $comment = '';
    public $uploaded_images = [];

    public function mount(Merchant $merchant, $return_order_id)
    {
        $this->merchant = $merchant;
        $status_allowed = ReturnOrderStatus::where(function ($query) {
            $query->whereHas('parent_status', function ($q) {
                $q->where('slug', 'return_in_progress'); 
            });
        })->firstOrFail();

        $this->return_order = $merchant->return_orders_through_products()
            ->where('return_orders.id', $return_order_id)
            ->where('return_order_status_id', $status_allowed->id)
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
                'product_order.payment_option',
                'reason',
            ])
            ->firstOrFail();
    }

    #[Computed]
    public function get_rejection_reasons()
    {
        return ReturnRejectionReason::orderBy('name')->toBase()->get();
    }

    #[On('updateImages')]
    public function updateImages($images)
    {
        $this->uploaded_images = $images;

        foreach ($this->uploaded_images as $key => $image) {
            $this->uploaded_images[$key]['image'] = new TemporaryUploadedFile($image['image'], config('filesystems.default'));
        }
    }

    public function reject_request()
    {
        $this->validate([
            'reason' => 'required|in:' . implode(',', $this->get_rejection_reasons->pluck('slug')->toArray()),
            'comment' => 'required|string|max:300',
            'uploaded_images' => 'array|min:1|max:5',
            'uploaded_images.*' => 'array:id,name,image,size,order',
            'uploaded_images.*.id' => 'nullable',
            'uploaded_images.*.name' => 'required',
            'uploaded_images.*.image' => 'required|image|mimes:png,jpg,jpeg|max:5120',
            'uploaded_images.*.size' => 'required',
            'uploaded_images.*.order' => 'required',
        ], [
            'reason.required' => 'Select a reason for rejecting the request.',
            'uploaded_images.min' => 'Please upload at least one image.',
            'uploaded_images.max' => 'Maximum of 5 images allowed.',
            'uploaded_images.*.array' => 'Error. Please refresh the page and try again.',
        ]);

        $return_rejection = new ReturnRejection;
        $return_rejection->return_order_id = $this->return_order->id;
        $return_rejection->return_rejection_reason_id = $this->get_rejection_reasons->where('slug', $this->reason)->first()->id;
        $return_rejection->comment = $this->comment;

        $log = new ReturnOrderLog;
        $log->return_order_id = $this->return_order->id;
        $log->title = "Return Request Rejected by Merchant";
        $log->description = "Merchant rejected the return request after the product has been returned. Reason: {$this->get_rejection_reasons->where('slug', $this->reason)->first()->name}";

        DB::beginTransaction();
        try {
            $return_rejection->save();

            foreach ($this->uploaded_images as $image) {
                $this->upload_file_media($return_rejection, $image['image'], 'return_rejection_images');
            }

            $rejected_status = ReturnOrderStatus::where('slug', 'pending_resolution')
                ->whereHas('parent_status', function ($q) {
                    $q->where('slug', 'rejected');
                })
                ->firstOrFail();

            $this->return_order->return_order_status_id = $rejected_status->id;
            $this->return_order->save();

            $log->return_order_status_id = $rejected_status->id;
            $log->save();

            DB::commit();
            $this->button_clickable = false;
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("RejectRequestAfterReturnModal.reject_request: {$th->getMessage()}");
            session()->flash('error', 'Failed to reject request after return');
            session()->flash('error_message', 'Something went wrong. Please try again later');
            return $this->button_clickable = true;
        }

        $this->dispatch('successModal', [
            'header' => 'Request rejected successfully',
        ]);
    }
    public function render()
    {
        return view('merchant.seller-center.logistics.return-orders.modals.reject-request-after-return-modal');
    }
}
