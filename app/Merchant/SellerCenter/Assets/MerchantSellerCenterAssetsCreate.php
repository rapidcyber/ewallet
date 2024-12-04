<?php

namespace App\Merchant\SellerCenter\Assets;

use App\Models\Merchant;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductCondition;
use App\Models\ProductDetail;
use App\Models\Warehouse;
use App\Traits\WithImageUploading;
use App\Traits\WithNumberGeneration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class MerchantSellerCenterAssetsCreate extends Component
{
    use WithFileUploads, WithImageUploading, WithNumberGeneration;

    #[Locked]
    public $image_count = 0;

    #[Locked]
    public $allowed_categories = [];

    #[Locked]
    public $image_replace_index = 0;

    public Merchant $merchant;

    public $replacement_image;

    public $mapAddress;

    public $mapLatlng;

    #[Locked]
    public $product_images = [];

    public $name = '';

    public $category;

    public $description = "";

    public $listed_price;

    public $condition;

    public $warehouse_stocks = [
        [
            'id' => '',
            'stock' => 0
        ]
    ];

    public $on_demand = false;

    public $package_weight;

    public $package_length;

    public $package_width;

    public $package_height;

    #[Locked]
    public $validate_active = false;

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;

        if ($merchant->warehouses()->count() == 0) {
            session()->flash('warning', 'No warehouses found. Please add warehouses first.');
            return redirect()->route('merchant.seller-center.assets.index', ['merchant' => $this->merchant]);
        }

        foreach ($this->product_categories() as $category) {
            foreach ($category->sub_categories as $sub_category) {
                $this->allowed_categories[] = $sub_category->id;
            }
        }
    }

    public function rules()
    {
        return [
            'product_images' => 'array|min:1|max:5',
            'product_images.*' => 'array:id,name,image,size,order',
            'product_images.*.id' => 'nullable',
            'product_images.*.name' => 'required',
            'product_images.*.image' => 'required|image|mimes:png,jpg,jpeg|max:5120',
            'product_images.*.size' => 'required',
            'product_images.*.order' => 'required',
            'name' => 'required|string|max:120',
            'category' => 'required|in:'.implode(',', $this->allowed_categories),
            'description' => 'required|string|max:2000',
            'on_demand' => 'boolean',
            'warehouse_stocks' => 'array|min:1|max:' . count($this->warehouses),
            'warehouse_stocks.*' => 'array:id,stock',
            'warehouse_stocks.*.id' => ['required', 'exists:warehouses,id', function ($attribute, $value, $fail) {
                $warehouse = Warehouse::where('id', $value)->with('availabilities')->first();
                if ($warehouse->merchant_id != $this->merchant->id) {
                    $fail('The selected warehouse does not belong to this merchant.');
                }

                if ($this->on_demand && count($warehouse->availabilities) == 0) {
                    $fail('The selected warehouses must have availability times set for on-demand delivery.');
                }
            }],
            'warehouse_stocks.*.stock' => 'required|numeric|min:1',
            'listed_price' => 'required|numeric|min:1|max:999999999999999.99',
            'condition' => 'required|in:'.implode(',', $this->product_conditions->pluck('slug')->toArray()),
            'package_weight' => 'required|numeric|gt:0',
            'package_length' => 'required|numeric|gt:0',
            'package_width' => 'required|numeric|gt:0',
            'package_height' => 'required|numeric|gt:0',
        ];
    }

    public function messages()
    {
        return [
            'warehouse_stocks.*.id.required' => 'Please choose a warehouse where your product is stored.',
            'warehouse_stocks.*.stock.required' => 'Please enter the quantity of this product in your warehouse.',
            'warehouse_stocks.*.stock.min' => 'Please ensure the quantity of this product in your warehouse is at least 1.',
        ];
    }

    public function updated($propertyName)
    {
        if ($this->validate_active) {
            $this->validateOnly($propertyName);
        }
    }

    #[Computed(persist: true)]
    public function product_categories()
    {
        return ProductCategory::whereNull('parent')
            ->with(['sub_categories' => function ($query) {
                $query->orderBy('name')
                ->select(['id', 'parent', 'name']);
            }])
            ->select(['id', 'parent', 'name'])
            ->orderBy('name')
            ->get();
    }
    
    #[Computed(persist: true)]
    public function warehouses()
    {
        return Warehouse::where('merchant_id', $this->merchant->id)->get(['id', 'name']);
    }

    #[Computed(persist: true)]
    public function product_conditions()
    {
        return ProductCondition::all();
    }

    #[On('updateProductImages')]
    public function updateProductImages($images)
    {
        $this->product_images = $images;

        foreach($this->product_images as $key => $image) {
            $this->product_images[$key]['image'] = new TemporaryUploadedFile($image['image'], config('filesystems.default'));
        }
    }

    public function add_location()
    {
        if (count(array_unique(array_column($this->warehouse_stocks, 'id'))) == count($this->warehouses)) {
            session()->flash('warning', 'No other warehouses available to add.');
            return;
        }

        $this->warehouse_stocks[] = [
            'id' => '',
            'stock' => 0
        ];
    }

    public function remove_location($index)
    {
        if (count($this->warehouse_stocks) > 1) {
            array_splice($this->warehouse_stocks, $index, 1);
        }   
    }

    private function check_duplicate_warehouse()
    {
        if (count(array_unique(array_column($this->warehouse_stocks, 'id'))) != count($this->warehouse_stocks)) {
            return true;
        }

        return false;
    }

    public function save()
    {
        try {
            $this->validate();
        } catch (ValidationException $ex) {
            $this->setErrorBag($ex->validator->errors());
            return $this->validate_active = true;
        }

        $this->validate_active = false;

        if ($this->check_duplicate_warehouse() === true) {
            session()->flash('warning', 'Duplicate warehouses found.');
            session()->flash('warning_message', 'Remove duplicate warehouses and try again.');
            return;
        }

        $product = new Product;

        $product->merchant_id = $this->merchant->id;
        $product->sku = $this->generate_product_sku($this->merchant);
        $product->product_category_id = $this->category;
        $product->name = $this->name;
        $product->description = $this->description;
        $product->price = $this->listed_price;

        $total_stocks = array_sum(array_column($this->warehouse_stocks, 'stock'));
        $product->stock_count = $total_stocks;
        $product->product_condition_id = $this->product_conditions()->where('slug', $this->condition)->first()->id;
        $product->on_demand = $this->on_demand;

        $product_details = new ProductDetail;

        $product_details->weight = $this->package_weight;
        $product_details->length = $this->package_length;
        $product_details->width = $this->package_width;
        $product_details->height = $this->package_height;

        try {
            DB::beginTransaction();
            $product->save();

            $product_details->product_id = $product->id;
            $product_details->save();

            foreach ($this->product_images as $image) {
                $this->upload_file_media($product, $image['image'], 'product_images');
            }

            foreach ($this->warehouse_stocks as $stock) {
                $product->warehouses()->attach($stock['id'], [
                    'stocks' => $stock['stock'],
                ]);
            }

            DB::commit();
            session()->flash('success', 'Product created successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('MerchantSellerCenterAssetsCreate.save: '.$th->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later.');

            return;
        }

        return $this->redirect(route('merchant.seller-center.assets.create', ['merchant' => $this->merchant]));
    }

    #[Layout('layouts.merchant.seller-center')]
    public function render()
    {
        return view('merchant.seller-center.assets.merchant-seller-center-assets-create');
    }
}
