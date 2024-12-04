<?php

namespace App\User\ReturnOrders;

use App\Models\ReturnOrder;
use App\Models\ReturnOrderDispute;
use App\Models\ReturnOrderLog;
use App\Models\ReturnOrderStatus;
use App\Models\User;
use App\Traits\WithImage;
use App\Traits\WithImageUploading;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class UserReturnOrdersDisputesModal extends Component
{
    use WithFileUploads, WithImageUploading, WithImage;

    public ReturnOrder $return_order;
    public $comment = '';
    public $uploaded_images = [];

    public function mount($return_order_id)
    {
        $this->return_order = ReturnOrder::whereHas('product_order', function ($q) {
            $q->where('buyer_id', auth()->id());
            $q->where('buyer_type', User::class);
        })
            ->with(['reason', 'product_order.product.first_image', 'product_order.product.merchant.logo'])
            ->findOrFail($return_order_id);
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
            'comment' => 'required|string|max:500',
            'uploaded_images' => 'array|max:5',
            'uploaded_images.*' => 'array:id,name,image,size,order',
            'uploaded_images.*.id' => 'nullable',
            'uploaded_images.*.name' => 'required',
            'uploaded_images.*.image' => 'required|image|mimes:png,jpg,jpeg|max:5120',
            'uploaded_images.*.size' => 'required',
            'uploaded_images.*.order' => 'required',
        ]);

        $dispute = new ReturnOrderDispute;
        $dispute->return_order_id = $this->return_order->id;
        $dispute->comment = $this->comment;
        
        DB::beginTransaction();
        try {
            $dispute->save();

            foreach($this->uploaded_images as $image) {
                $this->upload_file_media($dispute, $image['image'], 'dispute_images');
            }
            
            $dispute_in_progress = ReturnOrderStatus::where('name', 'Pending Response')->whereHas('parent_status', function ($q) {
                $q->where('name', 'Dispute In Progress');   
            })->firstOrFail();

            $this->return_order->return_order_status_id = $dispute_in_progress->id;
            $this->return_order->save();

            $log = new ReturnOrderLog;
            $log->return_order_id = $this->return_order->id;
            $log->return_order_status_id = $dispute_in_progress->id;
            $log->title = 'Buyer submitted a dispute';
            $log->description = "Buyer has submitted a dispute for return order #{$this->return_order->id}";
            $log->save();

            DB::commit();

            $this->dispatch('successSubmit', [
                'header' => 'Dispute submitted successfully.', 
                'message' => 'Please wait for the admin to resolve your dispute.'
            ]);
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('UserReturnOrderDispuitesModal.submit: ' . $ex);
            $this->dispatch('failedSubmit', [
                'header' => 'Dispute submission failed.', 
                'message' => 'Please try again later.'
            ]);
        }

        $this->dispatch('closeModal');
    }

    public function render()
    {
        return view('user.return-orders.user-return-orders-disputes-modal');
    }
}
