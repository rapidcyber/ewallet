<?php

namespace App\User\Orders;

use App\Models\EntityReview;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\User;
use App\Traits\WithImage;
use App\Traits\WithImageUploading;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class UserOrdersReviewModal extends Component
{
    use WithFileUploads, WithImageUploading, WithImage;

    public ProductOrder $product_order;
    public $rating = 0;
    public $comment = '';
    public $uploaded_images = [];

    public function mount($order_review_id)
    {
        $this->product_order = ProductOrder::whereHasMorph('buyer', User::class, function ($user) {
            $user->where('id', auth()->id());
        })
            ->whereHas('shipping_status', function ($query) {
                $query->where('slug', 'completed');
            })
            ->with(['product.merchant.logo', 'product.first_image'])
            ->findOrFail($order_review_id);
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
            'rating' => 'required|in:0,0.5,1,1.5,2,2.5,3,3.5,4,4.5,5',
            'comment' => 'required|string|max:300',
            'uploaded_images' => 'array|max:5',
            'uploaded_images.*' => 'array:id,name,image,size,order',
            'uploaded_images.*.id' => 'nullable',
            'uploaded_images.*.name' => 'required',
            'uploaded_images.*.image' => 'required|image|mimes:png,jpg,jpeg|max:5120',
            'uploaded_images.*.size' => 'required',
            'uploaded_images.*.order' => 'required',
        ]);

        $product_review = new EntityReview;
        $product_review->entity_id = $this->product_order->product->id;
        $product_review->entity_type = Product::class;
        $product_review->reviwer_id = auth()->id();
        $product_review->reviewer_type = User::class;
        $product_review->comment = $this->comment;
        $product_review->rating = $this->rating;

        DB::beginTransaction();
        try {
            $product_review->save();

            foreach($this->uploaded_images as $image) {
                $this->upload_file_media($product_review, $image['image'], 'review_images');
            }
            DB::commit();

            $this->dispatch('successSubmit', ['header' => 'Review Submitted', 'message' => 'Thank you for your feedback.']);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('UserOrdersReviewModal.submit: ' . $th->getMessage());
            $this->dispatch('failedSubmit', ['header' => 'Review Submission Failed', 'message' => 'Please try again later.']);
        }
    }

    public function render()
    {
        return view('user.orders.user-orders-review-modal');
    }
}
