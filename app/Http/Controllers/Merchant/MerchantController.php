<?php

namespace App\Http\Controllers\Merchant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Merchant\MerchantDetailRequest;
use App\Http\Requests\Merchant\MerchantWarehousesRequest;
use App\Http\Requests\Merchant\MerchantListRequest;
use App\Models\Merchant;
use App\Models\MerchantCategory;
use App\Traits\WithHttpResponses;
use App\Traits\WithImage;


class MerchantController extends Controller
{
    use WithHttpResponses, WithImage;

    /**
     * List categories for merchant
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function categories()
    {
        $categories = MerchantCategory::where('parent', null)
            ->with('sub_categories')
            ->get();

        return $this->success($categories);
    }

    /**
     * List merchant accounts of current user
     * 
     * @param \App\Http\Requests\Merchant\MerchantListRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function list(MerchantListRequest $request)
    {
        $validated = $request->validated();
        $per_page = $validated['per_page'] ?? 10;
        $page = $validated['page'] ?? 1;

        $merchants = auth()->user()->merchants()
            ->select(
                'id',
                'name',
                'status',
                'email',
                'merchant_category_id',
                'account_number',
                'phone_iso',
                'phone_number',
            )
            ->with(['category'])
            ->paginate(
                $per_page,
                ['*'],
                'merchants',
                $page,
            );

        return $this->success([
            'merchants' => $merchants->items(),
            'last_page' => $merchants->lastPage(),
            'total_item' => $merchants->total(),
        ]);
    }

    /**
     * Retrieve merchant information.
     * 
     * @param \App\Http\Requests\Merchant\MerchantDetailRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function details(MerchantDetailRequest $request)
    {
        $validated = $request->validated();
        $account_number = $validated['account_number'];

        $merchant = Merchant::where('account_number', $account_number)
            ->with('details')
            ->withAvg('reviews as rating', 'rating')
            ->withCount('reviews as reviews')
            ->first();

        $merchant->logo = $this->get_model_image($merchant, 'merchant_logo');
        $merchant->banner = $this->get_model_image($merchant->details, 'merchant_banner');
        $merchant->details->banner = $this->get_model_image($merchant->details, 'description_banner');

        // Don't return info if merchant is not owned and not verified.
        if (auth()->user()->id != $merchant->user_id and $merchant->status != 'verified') {
            return $this->error('Merchant not found', 499);
        }

        return $this->success($merchant);
    }

    /**
     * Summary of warehouses
     * @param \App\Http\Requests\Merchant\MerchantWarehousesRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function warehouses(MerchantWarehousesRequest $request)
    {
        $validated = $request->validated();
        $account_number = $validated['merc_ac'];

        $merchant = auth()->user()->merchants()->where('account_number', $account_number)->first();
        if (empty($merchant)) {
            return $this->error('Invalid merchant account number', 499);
        }

        $warehouses = $merchant->warehouses()->with(['location', 'availabilities'])->get();
        return $this->success($warehouses);
    }
}
