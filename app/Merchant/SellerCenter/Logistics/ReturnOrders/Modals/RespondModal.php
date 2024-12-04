<?php

namespace App\Merchant\SellerCenter\Logistics\ReturnOrders\Modals;

use App\Models\Merchant;
use App\Models\ReturnOrder;
use App\Models\ReturnOrderDisputeResponse;
use App\Models\ReturnOrderLog;
use App\Models\ReturnOrderStatus;
use App\Models\User;
use App\Traits\WithImage;
use App\Traits\WithImageUploading;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class RespondModal extends Component
{
    use WithImageUploading, WithImage;

    public Merchant $merchant;
    public ReturnOrder $return_order;

    #[Locked]
    public $button_clickable = true;

    public $comment = '';
    public $uploaded_images = [];

    public function mount(Merchant $merchant, $return_order_id)
    {
        $this->merchant = $merchant;
        $status_allowed = ReturnOrderStatus::where(function ($query) {
            $query->where('slug', 'pending_response');
        })->firstOrFail();

        $this->return_order = $merchant->return_orders_through_products()
            ->where('return_orders.id', $return_order_id)
            ->whereIn('return_order_status_id', $status_allowed)
            ->whereHas('dispute')
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
                'status.parent_status',
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

    public function submit_response()
    {
        $this->validate([
            'comment' => 'required|string|max:500',
            'uploaded_images' => 'array|min:1|max:5',
            'uploaded_images.*' => 'array:id,name,image,size,order',
            'uploaded_images.*.id' => 'nullable',
            'uploaded_images.*.name' => 'required',
            'uploaded_images.*.image' => 'required|image|mimes:png,jpg,jpeg|max:5120',
            'uploaded_images.*.size' => 'required',
            'uploaded_images.*.order' => 'required',
        ], [
            'uploaded_images.min' => 'Please upload at least one image.',
            'uploaded_images.max' => 'Maximum of 5 images allowed.',
            'uploaded_images.*.array' => 'Error. Please refresh the page and try again.',
        ]);

        DB::beginTransaction();
        try {
            $dispute_response = new ReturnOrderDisputeResponse;
            $dispute_response->return_order_dispute_id = $this->return_order->dispute->id;
            $dispute_response->comment = $this->comment;

            $dispute_response->save();

            foreach ($this->uploaded_images as $image) {
                $this->upload_file_media($dispute_response, $image['image'], 'dispute_response_images');
            }

            $status_resolution = ReturnOrderStatus::where(function ($query) {
                $query->where('slug', 'pending_resolution');
                $query->whereHas('parent_status', function ($query) {
                    $query->where('slug', 'dispute_in_progress');
                });
            })->firstOrFail();

            $this->return_order->return_order_status_id = $status_resolution->id;
            $this->return_order->save();

            $log = new ReturnOrderLog;
            $log->return_order_id = $this->return_order->id;
            $log->return_order_status_id = $status_resolution->id;
            $log->title = 'Seller responded to dispute';
            $log->description = 'Seller responded to the dispute and is waiting for the admin to give a resolution.';
            $log->save();

            $this->button_clickable = false;
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('MerchantSellerCenterLogisticsReturnOrders - RespondModal.refund - ' . $ex->getMessage());
            session()->flash('error', 'Failed to send response');
            session()->flash('error_message', 'Please try again later');
            $this->button_clickable = true;
            return;
        }

        $this->dispatch('successModal', [
            'header' => 'Response Submitted',
            'message' => 'Please wait for the admin to give a resolution.'
        ]);
    }

    public function render()
    {
        return view('merchant.seller-center.logistics.return-orders.modals.respond-modal');
    }
}
