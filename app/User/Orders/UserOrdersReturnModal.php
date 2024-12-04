<?php

namespace App\User\Orders;

use App\Models\ProductOrder;
use App\Models\ReturnOrder;
use App\Models\ReturnOrderLog;
use App\Models\ReturnOrderStatus;
use App\Models\ReturnReason;
use App\Models\User;
use App\Traits\WithImage;
use App\Traits\WithImageUploading;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class UserOrdersReturnModal extends Component
{
    use WithFileUploads, WithImageUploading, WithImage;

    public ProductOrder $product_order;
    public $visible = true;
    public $reason = '';
    public $comment = '';
    public $uploaded_images = [];

    public function mount($order_return_id) 
    {
        $this->product_order = auth()->user()->product_orders()
            ->whereHas('shipping_status', function ($query) {
                $query->where('slug', 'completed');
            })
            ->with(['product.merchant.logo', 'product.first_image', 'location', 'buyer' => function (MorphTo $query) {
                $query->morphWith([
                    User::class => ['profile'],
                ]);
            }])
            ->findOrFail($order_return_id);
    }

    #[Computed]
    public function reason_options() {
        return ReturnReason::orderBy('name')->select(['id', 'name', 'slug'])->toBase()->get();
    }

    #[On('updateImages')]
    public function updateImages($images)
    {
        $this->uploaded_images = $images;

        foreach ($this->uploaded_images as $key => $image) {
            $this->uploaded_images[$key]['image'] = new TemporaryUploadedFile($image['image'], config('filesystems.default'));
        }
    }

    public function submit()
    {
        $this->validate([
            'reason' => 'required|exists:return_reasons,slug',
            'comment' => 'required|string',
            'uploaded_images' => 'array|max:5',
            'uploaded_images.*' => 'array:id,name,image,size,order',
            'uploaded_images.*.id' => 'nullable',
            'uploaded_images.*.name' => 'required',
            'uploaded_images.*.image' => 'required|image|mimes:png,jpg,jpeg|max:5120',
            'uploaded_images.*.size' => 'required',
            'uploaded_images.*.order' => 'required',
        ]);

        $return_order = new ReturnOrder;
        $return_order->product_order_id = $this->product_order->id;
        $return_order->return_reason_id = $this->reason_options->where('slug', $this->reason)->first()->id;
        $return_order->comment = $this->comment;
        $return_order->return_order_status_id = ReturnOrderStatus::where('slug', str('Return Initiated')->slug('_'))->first()->id;

        DB::beginTransaction();
        try {
            $return_order->save();

            foreach ($this->uploaded_images as $image) {
                $this->upload_file_media($return_order, $image['image'], 'return_order_images');
            }

            $log = new ReturnOrderLog;
            $log->return_order_id = $return_order->id;
            $log->return_order_status_id = $return_order->return_order_status_id;
            $log->title = 'Buyer submitted a return request';
            $log->description = 'Buyer ' . $this->product_order->buyer->name . ' submitted a return request for the following reason: ' . $this->reason_options->where('slug', $this->reason)->first()->name;
            $log->save();

            DB::commit();
            $this->dispatch('successSubmit', ['header' => 'Return Request Submitted', 'message' => 'Please wait for the merchant to respond.']);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('UserOrdersReturnModal::submit - ' . $th);
            $this->dispatch('failedSubmit', ['header' => 'Return Request Failed', 'message' => 'Something went wrong. Please try again later.']);
        }

    }

    public function render()
    {
        return view('user.orders.user-orders-return-modal');
    }
}
