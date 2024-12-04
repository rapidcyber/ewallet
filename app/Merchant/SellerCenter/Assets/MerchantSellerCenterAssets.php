<?php

namespace App\Merchant\SellerCenter\Assets;

use App\Models\Merchant;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ShippingStatus;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithImage;
use App\Traits\WithImageUploading;
use App\Traits\WithNumberGeneration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class MerchantSellerCenterAssets extends Component
{
    use WithCustomPaginationLinks, WithFileUploads, WithImageUploading, WithNumberGeneration, WithPagination, WithImage;
    public Merchant $merchant;

    // table
    public $searchTerm = '';

    // Filter options
    public $main_categories = '';

    public $sub_categories = '';

    public $categories;

    public $main_category = '';

    public $sub_category = '';

    public $price_range = '';

    public $stock_range = '';

    public $approval_status = '';

    public $condition = '';

    public $shipping_status;

    public $orderByFieldName = 'created_at';

    public $orderBy = 'desc';

    #[Locked]
    public $can_create = false;

    #[Locked]
    public $can_edit = false;

    #[Locked]
    public $can_delete = false;

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;

        $this->categories = ProductCategory::with('parent_category')->whereHas('products', function ($query) {
            $query->where('merchant_id', $this->merchant->id);
        })->get();

        $this->main_categories = $this->categories->map(function ($category) {
            return $category->parent_category;
        })->unique();

        // dd($this->main_categories);
        $this->shipping_status = ShippingStatus::where('slug', 'completed')->first();

        if (Gate::allows('merchant-products', [$this->merchant, 'delete'])) {
            $this->can_delete = true;
        }

        if (Gate::allows('merchant-products', [$this->merchant, 'create'])) {
            $this->can_create = true;
        }

        if (Gate::allows('merchant-products', [$this->merchant, 'update'])) {
            $this->can_edit = true;
        }
    }

    #[Computed(persist: true)]
    public function has_warehouses()
    {
        return $this->merchant->warehouses()->count() > 0;
    }

    public function getSubCategories()
    {
        if (! empty($this->main_category)) {
            $this->sub_category = '';
            $this->sub_categories = $this->categories->where('parent', $this->main_category);
        } else {
            $this->sub_category = '';
            $this->sub_categories = null;
        }
    }

    public function sortTable($fieldName)
    {

        if ($this->orderByFieldName !== $fieldName) {
            $this->orderByFieldName = $fieldName;
            $this->orderBy = 'desc';
        } else {
            if ($this->orderBy === 'desc') {
                $this->orderBy = 'asc';
            } elseif ($this->orderBy === 'asc') {
                $this->orderBy = 'desc';
            }
        }
    }

    public function replicateProduct(Product $product)
    {
        $copyNumber = 1;

        $existingCount = Product::with('productDetail')->where('name', 'like', $product->name.' (%)%')
            ->orWhere('name', $product->name)
            ->count();

        if ($existingCount > 1) {
            $copyNumber++;
        }

        $newProduct = $product->replicate();
        $newProduct->name = $product->name.' ('.$copyNumber.')';
        $newProduct->sku = $this->generate_product_sku($this->merchant);
        $newProduct->stock_count = 0;
        $newProduct->sold_count = 0;
        $newProduct->is_featured = 0;
        $newProduct->is_active = 0;
        $newProduct->approval_status = 'review';

        DB::beginTransaction();
        try {
            $newProduct->save();

            $newProductDetails = $product->productDetail->replicate();
            $newProductDetails->product_id = $newProduct->id;
            $newProductDetails->save();

            $collection = 'product_images';
            $product_images = $product->getMedia($collection);

            foreach ($product_images as $image) {
                $this->copy_file_media($newProduct, $image, $collection);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('MerchantSellerCenterAssets.replicateProduct: '.$th->getMessage());
            session()->flash('error', 'Something went wrong!');

            return;
        }

        return session()->flash('success', 'Product replicated successfully!');
    }

    public function deleteProduct(Product $product)
    {
        if ($product->merchant_id !== $this->merchant->id) {
            return session()->flash('error', 'No product Found!');
        }
        
        $product_orders = $product->orders()->whereHas('shipping_status', function ($query) {
            $query->whereIn('slug', ['pending', 'packed', 'ready_to_ship']);
        })->count();

        if ($product_orders > 0) {
            session()->flash('error', 'Error: Unable to delete product.');
            session()->flash('error_message', 'Product has active orders. Please resolve the orders first.');
            return;
        }

        DB::beginTransaction();
        try {
            $product->delete();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('MerchantSellerCenterAssets.deleteProduct: '.$ex->getMessage());
            session()->flash('error', 'Something went wrong! Please try again later.');
            return;
        }
    }

    public function update_active_status($product_sku)
    {
        $product = $this->merchant->owned_products()->where('sku', $product_sku)->first();

        if (!$product || $product->merchant_id != $this->merchant->id) {
            return session()->flash('error', 'No product Found!');
        }

        if ($product->approval_status === 'approved') {
            $product->is_active = ! $product->is_active;
            $product->save();
        }
    }

    #[Layout('layouts.merchant.seller-center')]
    public function render()
    {
        $collection = 'product_images';
        $products = $this->merchant->owned_products()
            ->select([
                'id',
                'sku',
                'name',
                'stock_count',
                'price',
                'is_active',
                'merchant_id',
                'sold_count',
                'approval_status',
                'created_at',
            ])
            ->with([
                'first_image'
            ])
            ->withCount(['orders' => function ($orders) {
                $orders->where('shipping_status_id', $this->shipping_status->id); // Completed
            }]);

        if (! empty($this->searchTerm)) {
            $products = $products->where(function ($query) {
                $query->where('name', 'like', '%'.$this->searchTerm.'%')
                    ->orWhere('sku', 'like', '%'.$this->searchTerm.'%');
            });
        }

        if (! empty($this->main_category)) {
            $sub_categories = ProductCategory::where('parent', $this->main_category)->pluck('id')->toArray();
            $products = $products->whereIn('product_category_id', $sub_categories);
        }

        if (! empty($this->sub_category)) {
            $products = $products->where('product_category_id', $this->sub_category);
        }

        if (! empty($this->price_range)) {
            $price_range = explode('-', $this->price_range);

            if (! empty($price_range[1])) {
                $products = $products->whereBetween('price', $price_range);
            } else {
                $products = $products->where('price', '>=', $price_range[0]);
            }
        }

        if ($this->stock_range !== null) {
            $stock_range = explode('-', $this->stock_range);

            if (! empty($stock_range[1])) {
                $products = $products->whereBetween('stock_count', $stock_range);
            } elseif ($stock_range[0] == 0) {
                $products = $products->where('stock_count', 0);
            } else {
                $products = $products->where('stock_count', '>=', $stock_range[0]);
            }
        }

        if (! empty($this->approval_status)) {
            $products = $products->where('approval_status', $this->approval_status);
        }

        if (! empty($this->condition)) {
            $products = $products->whereHas('condition', function ($query) {
                $query->where('slug', $this->condition);
            });
        }

        $products = $products->orderBy($this->orderByFieldName, $this->orderBy)->paginate(10);

        $elements = $this->getPaginationElements($products);

        return view('merchant.seller-center.assets.merchant-seller-center-assets-list', ['elements' => $elements, 'products' => $products]);
    }
}
