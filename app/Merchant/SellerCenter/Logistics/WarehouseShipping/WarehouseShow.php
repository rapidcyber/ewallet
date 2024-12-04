<?php

namespace App\Merchant\SellerCenter\Logistics\WarehouseShipping;

use App\Models\Merchant;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductCondition;
use App\Models\Warehouse;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithImage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class WarehouseShow extends Component
{
    use WithPagination, WithCustomPaginationLinks, WithoutUrlPagination, WithImage;

    public Merchant $merchant;
    public Warehouse $warehouse;
    public $searchTerm = '';
    public $main_category = '';
    public $sub_category = '';
    public $price_range = '';
    public $stock_range = '';
    public $condition = '';
    public $approval_status = '';


    protected $allowedPriceRange = [
        "",
        "501-1000",
        "1001-2500",
        "2501-5000",
        "5001-10000",
        "10001-20000",
        "20001-50000",
        "50000"
    ];

    protected $allowedStockRange = [
        "",
        "0",
        "1-10",
        "11-50",
        "51-100",
        "101-200",
        "201-500",
        "501-1000",
        "1001-5000",
        "5001-10000",
        "10000"
    ];

    public function mount($merchant_id, $warehouse_id)
    {
        $this->merchant = Merchant::find($merchant_id);
        $this->warehouse = Warehouse::where('merchant_id', $merchant_id)->find($warehouse_id);
    }

    #[Computed]
    public function main_categories()
    {
        return ProductCategory::whereHas('sub_categories', function ($query) {
            $query->whereHas('products', function ($query) {
                $query->where('merchant_id', $this->merchant->id);
                $query->whereHas('warehouses', function ($query) {
                    $query->where('warehouses.id', $this->warehouse->id);
                });
            });
        })
            ->with(['sub_categories' => function ($query) {
                $query->whereHas('products', function ($query) {
                    $query->where('merchant_id', $this->merchant->id);
                    $query->whereHas('warehouses', function ($query) {
                        $query->where('warehouses.id', $this->warehouse->id);
                    });
                });
            }])
            ->get();
    }

    #[Computed]
    public function sub_categories()
    {
        $main_category = $this->main_category;
        if ($main_category and in_array($main_category, $this->main_categories->pluck('slug')->toArray())) {
            return $this->main_categories->where('slug', $main_category)->first()->sub_categories;
        } else {
            return [];
        }
    }

    #[Computed]
    public function product_conditions()
    {
        return ProductCondition::pluck('name', 'slug')->toArray();
    }

    public function updated($value)
    {
        $this->resetPage();
    }

    private function validate_filters()
    {
        if ($this->main_category and !in_array($this->main_category, $this->main_categories->pluck('slug')->toArray())) {
            $this->main_category = '';
        }

        if ($this->main_category and $this->sub_category and !in_array($this->sub_category, $this->sub_categories->pluck('slug')->toArray())) {
            $this->sub_category = '';
        }

        if ($this->price_range and !in_array($this->price_range, $this->allowedPriceRange)) {
            $this->price_range = '';
        }

        if ($this->stock_range and !in_array($this->stock_range, $this->allowedStockRange)) {
            $this->stock_range = '';
        }

        if ($this->condition and !in_array($this->condition, array_keys($this->product_conditions))) {
            $this->condition = '';
        }

        if ($this->approval_status and !in_array($this->approval_status, ['', 'review', 'approved', 'rejected', 'suspended'])) {
            $this->approval_status = '';
        }
    }

    public function render()
    {
        $this->validate_filters();

        $products = Product::whereHas('warehouses', function ($query) {
            $query->where('warehouses.id', $this->warehouse->id);
        })
            ->select([
                'id',
                'name',
                'price'
            ])
            ->with(['first_image'])
            ->withSum(['warehouses as stocks' => function ($query) {
                $query->where('warehouses.id', $this->warehouse->id);
            }], 'product_warehouse.stocks');

        if ($this->searchTerm) {
            $products = $products->where('name', 'like', '%' . $this->searchTerm . '%');
        }

        if ($this->main_category) {
            $products = $products->whereHas('category', function ($query) {
                $query->whereHas('parent_category', function ($query) {
                    $query->where('slug', $this->main_category);
                });
            });
        }

        if ($this->sub_category) {
            $products = $products->whereHas('category', function ($query) {
                $query->where('slug', $this->sub_category);
            });
        }

        if ($this->price_range) {
            $price = explode('-', $this->price_range);

            if (count($price) == 2) {
                $products = $products->whereBetween('price', $price);
            }

            if (count($price) == 1) {
                $products = $products->where('price', '>=', $price[0]);
            }
        }

        if ($this->stock_range) {
            $stock = explode('-', $this->stock_range);

            if (count($stock) == 2) {
                $products = $products->whereBetween('stock_count', $stock);
            }

            if (count($stock) == 1) {
                $products = $products->where('stock_count', '>=', $stock[0]);
            }
        }

        if ($this->condition) {
            $products = $products->whereHas('condition', function ($query) {
                $query->where('slug', $this->condition);
            });
        }

        if ($this->approval_status) {
            $products = $products->where('approval_status', $this->approval_status);
        }

        $products = $products->paginate(6);
        $elements = $this->getPaginationElements($products);

        return view('merchant.seller-center.logistics.warehouse-shipping.warehouse-show')->with([
            'products' => $products,
            'elements' => $elements
        ]);
    }
}
