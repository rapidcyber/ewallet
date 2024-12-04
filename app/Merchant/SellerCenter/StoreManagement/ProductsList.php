<?php

namespace App\Merchant\SellerCenter\StoreManagement;

use App\Models\Merchant;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithImage;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductsList extends Component
{
    use WithCustomPaginationLinks, WithPagination, WithImage;

    public Merchant $merchant;

    public Product $product;

    public $categories;

    public $main_categories;

    public $sub_categories;

    public $searchTerm = '';

    public $category = '';

    public $main_category = '';

    public $sub_category = '';

    public $condition = '';

    public $ranges = [
        'min_price' => 0,
        'max_price' => 50000,
        'min_stock' => 0,
        'max_stock' => 50000,
    ];

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;

        $this->categories = ProductCategory::with('parent_category')->whereHas('products', function ($query) {
            $query->where('merchant_id', $this->merchant->id);
        })->get();

        $this->main_categories = $this->categories->map(function ($category) {
            return $category->parent_category;
        })->unique();
    }

    public function getProductSubCategories()
    {
        if (! empty($this->main_category)) {
            $this->sub_category = '';
            $this->sub_categories = $this->categories->where('parent', $this->main_category);
        } else {
            $this->sub_category = '';
            $this->sub_categories = null;
        }
    }

    public function clear_product_filter()
    {
        $this->searchTerm = '';
        $this->clear_main_category();
        $this->clear_sub_category();
        $this->clear_condition();
        $this->ranges['min_price'] = 0;
        $this->ranges['max_price'] = 50000;
        $this->ranges['min_stock'] = 0;
        $this->ranges['max_stock'] = 50000;
    }

    public function clear_main_category()
    {
        $this->main_category = '';
        $this->sub_categories = null;
        $this->sub_category = '';
    }

    public function clear_sub_category()
    {
        $this->sub_category = '';
    }

    public function clear_condition()
    {
        $this->condition = '';
    }

    public function product_feature_change(Product $product)
    {
        $product->is_featured = empty($product->is_featured);
        $product->save();

        $this->dispatch('featuredProductsUpdated');
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'searchTerm') {
            $this->resetPage();
        } elseif ($propertyName === 'main_category') {
            $this->resetPage();
        } elseif ($propertyName === 'sub_category') {
            $this->resetPage();
        } elseif ($propertyName === 'condition') {
            $this->resetPage();
        } elseif (str_starts_with($propertyName, 'ranges.')) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $products = $this->merchant->owned_products()->with('first_image');
        if ($this->searchTerm) {
            $products = $products->where('name', 'like', '%'.$this->searchTerm.'%');
        }

        if ($this->main_category) {
            $sub_categories = ProductCategory::where('parent', $this->main_category)->pluck('id')->toArray();
            $products = $products->whereIn('product_category_id', $sub_categories);
        }

        if ($this->sub_category) {
            $products = $products->where('product_category_id', $this->sub_category);
        }

        if ($this->condition) {
            $products = $products->whereHas('condition', function ($query) {
                $query->where('slug', $this->condition);
            });
        }

        $products = $products->whereBetween('price', [
            $this->ranges['min_price'],
            $this->ranges['max_price'],
        ]);

        $products = $products->whereBetween('stock_count', [
            $this->ranges['min_stock'],
            $this->ranges['max_stock'],
        ]);

        $products = $products->orderBy('created_at')->paginate(5);

        $products_elements = $this->getPaginationElements($products);
        $this->resetPage();

        return view('merchant.seller-center.store-management.products-list', ['products_elements' => $products_elements, 'products' => $products]);
    }
}
