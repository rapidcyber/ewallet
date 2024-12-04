<?php

namespace App\Http\Controllers\Review;

use App\Http\Controllers\Controller;
use App\Http\Requests\Review\MerchantReviewListRequest;
use App\Http\Requests\Review\MerchantReviewRequest;
use App\Http\Requests\Review\ProductReviewListRequest;
use App\Http\Requests\Review\ProductReviewRequest;
use App\Http\Requests\Review\ServiceReviewListRequest;
use App\Http\Requests\Review\ServiceReviewRequest;
use App\Models\EntityReview;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use App\Traits\WithEntity;
use App\Traits\WithHttpResponses;
use Illuminate\Support\Facades\DB;
use Exception;

class ReviewController extends Controller
{

    use WithHttpResponses, WithEntity;

    /**
     * Summary of product_review
     * 
     * @param \App\Http\Requests\Review\ProductReviewRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function product_review(ProductReviewRequest $request)
    {
        $validated = $request->validated();
        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $product = Product::where('sku', $validated['sku'])->first();

        /// Owner merchant is not allowed to review their own products.
        if (get_class($entity) == Merchant::class && $entity->id == $product->merchant_id) {
            return $this->error('Invalid product SKU', 499);
        }

        $can_review = $entity->reviews()->where([
            'entity_id' => $product->id,
            'entity_type' => Product::class,
        ])->exists() == false;
        if ($can_review == false) {
            return $this->errorFromCode('max_product_review');
        }

        $review = new EntityReview;
        $review->fill([
            'comment' => $validated['comment'],
            'rating' => $validated['rating'],
            'entity_id' => $product->id,
            'entity_type' => get_class($product),
            'reviewer_id' => $entity->id,
            'reviewer_type' => get_class($entity),
        ]);


        try {
            DB::transaction(function () use ($review) {
                $review->save();
            });

            return $this->success([
                'review_count' => $product->reviews()->count(),
                'avg_rating' => round($product->reviews()->average('rating'), 1),
                'review' => $review,
            ]);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     * Summary of service_review
     * 
     * @param \App\Http\Requests\Review\ProductReviewRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function service_review(ServiceReviewRequest $request)
    {
        $validated = $request->validated();
        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $service = Service::find($validated['id']);

        /// Owner merchant is not allowed to review their own services.
        if (get_class($entity) == Merchant::class && $entity->id == $service->merchant_id) {
            return $this->error('Invalid service ID', 499);
        }

        $review = new EntityReview;
        $review->fill([
            'comment' => $validated['comment'],
            'rating' => $validated['rating'],
            'entity_id' => $service->id,
            'entity_type' => get_class($service),
            'reviewer_id' => $entity->id,
            'reviewer_type' => get_class($entity),
        ]);


        try {
            DB::transaction(function () use ($review) {
                $review->save();
            });

            return $this->success([
                'review_count' => $service->reviews()->count(),
                'avg_rating' => round($service->reviews()->average('rating'), 1),
                'review' => $review,
            ]);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     * Summary of merchant_review
     * 
     * @param \App\Http\Requests\Review\MerchantReviewRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function merchant_review(MerchantReviewRequest $request)
    {
        $validated = $request->validated();
        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        /// Owner merchant is not allowed to review their own.
        if (get_class($entity) == Merchant::class && $entity->id == $validated['account_number']) {
            return $this->error('Invalid merchant.', 499);
        }

        $merchant = Merchant::where('account_number', $validated['account_number'])->first();

        $review = new EntityReview;
        $review->fill([
            'comment' => $validated['comment'],
            'rating' => $validated['rating'],
            'entity_id' => $merchant->id,
            'entity_type' => get_class($merchant),
            'reviewer_id' => $entity->id,
            'reviewer_type' => get_class($entity),
        ]);


        try {
            DB::transaction(function () use ($review) {
                $review->save();
            });

            return $this->success([
                'review_count' => $merchant->reviews()->count(),
                'avg_rating' => round($merchant->reviews()->average('rating'), 1),
                'review' => $review,
            ]);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }


    /**
     * Summary of list_product
     * 
     * @param \App\Http\Requests\Review\ProductReviewListRequest $request
     * @return void
     */
    public function list_product(ProductReviewListRequest $request)
    {
        $validated = $request->validated();
        $per_page = $validated['per_page'] ?? 10;
        $page = $validated['page'] ?? 1;

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $query = EntityReview::whereHasMorph('entity', Product::class, function ($q) use ($validated) {
            $q->where('sku', $validated['sku']);
        })->orderByDesc('created_at');

        $my_review = EntityReview::where([
            'reviewer_id' => $entity->id,
            'reviewer_type' => get_class($entity),
        ])->first();

        $average = $query->average('rating');
        $reviews = $query->paginate(
            $per_page,
            ['*'],
            'reviews',
            $page
        );

        return $this->success([
            'my_review' => $my_review,
            'average' => round($average, 1),
            'reviews' => $reviews->items(),
            'last_page' => $reviews->lastPage(),
            'total_item' => $reviews->total(),
        ]);
    }

    /**
     * Summary of list_service
     * 
     * @param \App\Http\Requests\Review\ServiceReviewListRequest $request
     * @return void
     */
    public function list_service(ServiceReviewListRequest $request)
    {
        $validated = $request->validated();
        $per_page = $validated['per_page'] ?? 10;
        $page = $validated['page'] ?? 1;

        $query = EntityReview::whereHasMorph('entity', Service::class, function ($q) use ($validated) {
            $q->where('id', $validated['id']);
        })->orderByDesc('created_at');

        $average = $query->average('rating');
        $reviews = $query->paginate(
            $per_page,
            ['*'],
            'reviews',
            $page
        );

        return $this->success([
            'average' => round($average, 1),
            'reviews' => $reviews->items(),
            'last_page' => $reviews->lastPage(),
            'total_item' => $reviews->total(),
        ]);
    }

    /**
     * Summary of list_merchant
     * 
     * @param \App\Http\Requests\Review\MerchantReviewListRequest $request
     * @return void
     */
    public function list_merchant(MerchantReviewListRequest $request)
    {
        $validated = $request->validated();
        $per_page = $validated['per_page'] ?? 10;
        $page = $validated['page'] ?? 1;

        $query = EntityReview::whereHasMorph('entity', Merchant::class, function ($q) use ($validated) {
            $q->where('account_number', $validated['account_number']);
        })->orderByDesc('created_at');

        $average = $query->average('rating');
        $reviews = $query->paginate(
            $per_page,
            ['*'],
            'reviews',
            $page
        );

        return $this->success([
            'average' => round($average, 1),
            'reviews' => $reviews->items(),
            'last_page' => $reviews->lastPage(),
            'total_item' => $reviews->total(),
        ]);
    }
}
