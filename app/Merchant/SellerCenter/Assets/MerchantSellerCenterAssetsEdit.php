<?php

namespace App\Merchant\SellerCenter\Assets;

use App\Models\Merchant;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductCondition;
use App\Models\ProductDetail;
use App\Models\Warehouse;
use App\Traits\WithImage;
use App\Traits\WithImageUploading;
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
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MerchantSellerCenterAssetsEdit extends Component
{
    use WithFileUploads, WithImageUploading, WithImage;

    #[Locked]
    public $allowed_categories = [];

    public Merchant $merchant;
    public Product $product;
    public ProductDetail $productDetail;

    #[Locked]
    public $product_images = [];
    
    public $name, $sku, $category, $description;
    public $listed_price, $condition;
    public $package_weight, $package_length, $package_width, $package_height;
    public $warehouse_stocks = [];
    public $on_demand = false;

    #[Locked]
    public $delete_images = [];

    #[Locked]
    public $validate_active = false;

    #[Locked]
    public $assetEdit = true;

    public function mount(Merchant $merchant, Product $product)
    {
        foreach ($this->product_categories() as $category) {
            foreach ($category->sub_categories as $sub_category) {
                $this->allowed_categories[] = $sub_category->id;
            }
        }

        $this->merchant = $merchant;
        $this->product = $product->load(['condition', 'warehouses', 'productDetail']);
        $this->productDetail = $product->productDetail;

        $this->name = $product->name;
        $this->sku = $product->sku;
        $this->category = $product->product_category_id;
        $this->description = $product->description;
        $this->listed_price = $product->price;
        $this->condition = $product->condition->slug;
        $this->on_demand = $product->on_demand;
        $this->package_weight = $product->productDetail->weight;
        $this->package_length = $product->productDetail->length;
        $this->package_width = $product->productDetail->width;
        $this->package_height = $product->productDetail->height;


        $product_warehouses = $product->warehouses;

        foreach ($product_warehouses as $product_warehouse) {
            $this->warehouse_stocks[] = [
                'id' => $product_warehouse->id,
                'stock' => $product_warehouse->pivot->stocks
            ];
        }

        $product_images = $product->getMedia('product_images');
        foreach ($product_images as $product_image) {            
            $this->product_images[] = [
                'name' => $product_image->name,
                'image' => $this->get_media_url($product_image),
                'size' => $product_image->human_readable_size,
                'id' => $product_image->id,
                'order' => $product_image->order_column
            ];
        }
    }

    public function rules()
    {
        return [
            'product_images' => 'array|min:1|max:5',
            'product_images.*' => 'array:id,name,image,size,order',
            'product_images.*.id' => 'nullable',
            'product_images.*.name' => 'required',
            // 'product_images.*.image' => 'required|image|mimes:png,jpg,jpeg|max:5120',
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
            'condition' => 'required|in:' . implode(',', $this->product_conditions->pluck('slug')->toArray()),
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

    #[On('updateProductImages')]
    public function updateProductImages($images)
    {
        $this->product_images = $images;

        foreach($this->product_images as $key => $image) {
            $this->product_images[$key]['image'] = $this->product_images[$key]['id'] ? $this->product_images[$key]['image'] : new TemporaryUploadedFile($image['image'], config('filesystems.default'));
        }
    }

    #[On('updateDeletedImages')]
    public function updateDeletedImages($images)
    {
        $this->delete_images = $images;
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
        return $this->merchant->warehouses;
    }

    #[Computed(persist: true)]
    public function product_conditions()
    {
        return ProductCondition::all();
    }

    public function add_location()
    {
        if (count(array_unique(array_column($this->warehouse_stocks, 'id'))) == count($this->warehouses())) {
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

        $this->product->product_category_id = $this->category;
        $this->product->name = $this->name;
        $this->product->description = $this->description;
        $this->product->price = $this->listed_price;

        $total_stocks = array_sum(array_column($this->warehouse_stocks, 'stock'));
        $this->product->stock_count = $total_stocks;
        $this->product->product_condition_id = $this->product_conditions()->where('slug', $this->condition)->first()->id;
        $this->product->on_demand = $this->on_demand;

        $this->productDetail->weight = $this->package_weight;
        $this->productDetail->length = $this->package_length;
        $this->productDetail->width = $this->package_width;
        $this->productDetail->height = $this->package_height;

        try {
            DB::beginTransaction();
            $this->product->save();
            $this->productDetail->save();

            $stocks = [];
            foreach ($this->warehouse_stocks as $stock) {
                $stocks[$stock['id']] = [
                    'stocks' => $stock['stock'],
                ];
            }

            $this->product->warehouses()->sync($stocks);

            foreach ($this->delete_images as $image) {
                if ($image['id'] !== null) {
                    $media = Media::find($image['id']);
                    $media->delete();
                }
            }

            $media_ids = [];

            foreach ($this->product_images as $key => $product_image) {
                if ($product_image['id'] == null) {
                    $media = $this->upload_file_media($this->product, $product_image['image'], 'product_images');
                    $media->save();
                    $media_ids[] = $media->id;
                } else {
                    $media_ids[] = $product_image['id'];
                }

            }

            Media::setNewOrder($media_ids);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('MerchantSellerCenterAssetsEdit::save - ' . $th);
            session()->flash('error', 'Something went wrong. Please try again later.');
            return;
        }

        session()->flash('success', 'Product updated successfully.');
        return $this->redirect(route('merchant.seller-center.assets.edit', ['merchant' => $this->merchant, 'product' => $this->product]));
    }

    #[Layout('layouts.merchant.seller-center')]
    public function render()
    {
        return view('merchant.seller-center.assets.merchant-seller-center-assets-create');
    }
}
