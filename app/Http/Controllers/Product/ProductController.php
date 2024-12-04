<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\LocationListRequest;
use App\Http\Requests\Product\ProductActiveCategoriesRequest;
use App\Http\Requests\Product\ProductDetailsRequest;
use App\Http\Requests\Product\ProductEnlistRequest;
use App\Http\Requests\Product\ProductListRequest;
use App\Http\Requests\Product\ProductOwnedRequest;
use App\Http\Requests\Product\UpdateProductInfoRequest;
use App\Models\Merchant;
use App\Models\ProductDetail;
use App\Models\Warehouse;
use App\Traits\WithImage;
use App\Traits\WithImageUploading;
use App\Traits\WithNumberGeneration;
use Illuminate\Support\Facades\DB;
use App\Traits\WithHttpResponses;
use App\Models\ProductCondition;
use App\Models\ProductCategory;
use App\Traits\WithEntity;
use App\Models\Product;
use Exception;

class ProductController extends Controller
{

    use WithHttpResponses, WithEntity, WithNumberGeneration, WithImage, WithImageUploading;

    /**
     * Get list of products.
     *  - all or by merchant (if account_number is provided)
     * 
     * @param \App\Http\Requests\Product\ProductListRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function list(ProductListRequest $request)
    {
        $validated = $request->validated();
        $per_page = $validated['per_page'] ?? 10;
        $page = $validated['page'] ?? 1;
        $category = $validated['category'] ?? null;
        $featured = $validated['featured'] ?? null;

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $query = Product::select(
            'products.id',
            'sku',
            'product_category_id',
            'name',
            'price',
            'sold_count',
            'merchant_id',
            'is_featured',
            'on_demand',
        )
            ->withAvg('reviews as rating', 'rating')
            ->withCount('reviews as reviews')
            ->with(['category'])
            ->where([
                'approval_status' => 'approved',
                'is_active' => 1,
            ]);
        if (!empty($featured)) {
            $query->where('is_featured', $featured);
        }

        if (!empty($category)) {
            $query = $query->whereHas('category', function ($q) use ($category) {
                $q->where('slug', $category);
            });
        }

        if (empty($validated['account_number']) == false) {
            $query = $query->whereHas('merchant', function ($q) use ($validated) {
                $q->where('account_number', $validated['account_number']);
            });
        }

        $products = $query->paginate(
            $per_page,
            ['*'],
            'products',
            $page
        );

        foreach ($products->items() as $product) {
            $product->is_mine = $this->is_mine($product, $entity);
            $this->add_model_images($product, 'product_images', true);
        }

        return $this->success([
            'products' => $products->items(),
            'last_page' => $products->lastPage(),
            'total_item' => $products->total(),
        ]);
    }

    /**
     * Summary of locations
     * @param \App\Http\Requests\Product\LocationListRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function by_locations(LocationListRequest $request)
    {
        $validated = $request->validated();
        $latitude = $validated['latitude'] ?? null;
        $longitude = $validated['longitude'] ?? null;
        $radius = $validated['radius'] ?? null;
        $location_id = $validated['location_id'] ?? null;

        $per_page = $validated['per_page'] ?? 10;
        $page = $validated['page'] ?? 1;

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        if (empty($location_id) == false) {
            $warehouse = Warehouse::whereHas('location', function ($q) use ($location_id) {
                $q->where('locations.id', $location_id);
            })->first();

            if (empty($warehouse)) {
                return $this->error('Invalid location ID', 499);
            }

            $products = Product::select(
                'products.id',
                'sku',
                'product_category_id',
                'name',
                'price',
                'sold_count',
                'merchant_id',
                'is_featured',
                'on_demand',
            )
                ->whereHas('warehouses', function ($q) use ($warehouse) {
                    $q->where('warehouses.id', $warehouse->id);
                })
                ->where([
                    'approval_status' => 'approved',
                    'is_active' => 1,
                ])
                ->withAvg('reviews as rating', 'rating')
                ->withCount('reviews as reviews')
                ->with(['category'])
                ->paginate(
                    $per_page,
                    ['*'],
                    'products',
                    $page
                );

            foreach ($products->items() as $product) {
                $product->is_mine = $this->is_mine($product, $entity);
                $this->add_model_images($product, 'product_images', true);
            }

            return $this->success([
                'products' => $products->items(),
                'last_page' => $products->lastPage(),
                'total_item' => $products->total(),
            ]);

        } else {
            $query = Warehouse::select('warehouses.id');
            $locations = $query->whereHas('products', function ($q) {
                $q->where([
                    'approval_status' => 'approved',
                    'is_active' => 1,
                ]);
            })
                ->join('locations', function ($q) {
                    $q->on('locations.entity_id', '=', 'warehouses.id')
                        ->where('locations.entity_type', '=', Warehouse::class);
                })
                ->selectRaw('locations.id as location_id, latitude, longitude, ST_DISTANCE_SPHERE(
                POINT(?,?),
                POINT(longitude,latitude)
                ) AS distance', [$longitude, $latitude])
                ->having('distance', '<', $radius)
                ->orderBy('distance')
                ->withCount([
                    'products' => function ($q) {
                        $q->where([
                            'approval_status' => 'approved',
                            'is_active' => 1,
                        ]);
                    }
                ])->get();

            return $this->success($locations);
        }
    }

    /**
     * Return list of owned products by merchant.
     * 
     * @param \App\Http\Requests\Product\ProductOwnedRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function owned(ProductOwnedRequest $request)
    {
        $validated = $request->validated();
        $latitude = $validated['latitude'] ?? null;
        $longitude = $validated['longitude'] ?? null;
        $radius = $validated['radius'] ?? null;
        $location_id = $validated['location_id'] ?? null;

        $per_page = $validated['per_page'] ?? 10;
        $page = $validated['page'] ?? 1;

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        if (empty($location_id) == false) {
            if (get_class($entity) == Merchant::class) {
                $warehouse = $entity->warehouses()->whereHas('location', function ($q) use ($location_id) {
                    $q->where('id', $location_id);
                })->first();
            } else {
                $warehouse = Warehouse::whereHas('merchant', function ($q) use ($entity) {
                    $q->where('user_id', $entity->id);
                })->whereHas('location', function ($q) use ($location_id) {
                    $q->where('id', $location_id);
                })->first();
            }

            if (empty($warehouse)) {
                return $this->error('Invalid location ID', 499);
            }

            $products = Product::select(
                'products.id',
                'sku',
                'product_category_id',
                'name',
                'price',
                'sold_count',
                'merchant_id',
                'is_active',
                'approval_status as status',
            )
                ->whereHas('warehouses', function ($q) use ($warehouse) {
                    $q->where('warehouses.id', $warehouse->id);
                })
                ->where([
                    'approval_status' => 'approved',
                    'is_active' => 1,
                ])
                ->withAvg('reviews as rating', 'rating')
                ->withCount('reviews as reviews')
                ->with(['category'])
                ->paginate(
                    $per_page,
                    ['*'],
                    'products',
                    $page
                );

            foreach ($products->items() as $product) {
                $product->is_mine = $this->is_mine($product, $entity);
                $this->add_model_images($product, 'product_images', true);
            }

            return $this->success([
                'products' => $products->items(),
                'last_page' => $products->lastPage(),
                'total_item' => $products->total(),
            ]);
        } else {
            $query = null;
            if (get_class($entity) == Merchant::class) {
                $query = $entity->warehouses()->select(['warehouses.id', 'name']);
            } else {
                $query = Warehouse::whereHas('merchant', function ($q) use ($entity) {
                    $q->where('user_id', $entity->id);
                })->select(['warehouses.id', 'name']);
            }

            $locations = $query
                ->whereHas('products', function ($q) {
                    $q->where([
                        'approval_status' => 'approved',
                        'is_active' => 1,
                    ]);
                })
                ->join('locations', function ($q) {
                    $q->on('locations.entity_id', '=', 'warehouses.id')
                        ->where('locations.entity_type', '=', Warehouse::class);
                })
                ->selectRaw('locations.id as location_id, latitude, longitude, ST_DISTANCE_SPHERE(
                    POINT(?,?),
                    POINT(longitude,latitude)
                    ) AS distance', [$longitude, $latitude])
                ->having('distance', '<', $radius)
                ->orderBy('distance')
                ->withCount([
                    'products' => function ($q) {
                        $q->where([
                            'approval_status' => 'approved',
                            'is_active' => 1,
                        ]);
                    }
                ])
                ->get();

            return $this->success($locations);
        }
    }

    /**
     * Get product categories.
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function categories()
    {
        $categories = ProductCategory::where('parent', null)
            ->with('sub_categories')
            ->get();

        return $this->success($categories);
    }

    /**
     * Enlist a product.
     * 
     * @param \App\Http\Requests\Product\ProductEnlistRequest $request
     * @return void
     * 
     * @TODO: add on_demand
     */
    public function enlist(ProductEnlistRequest $request)
    {
        $validated = $request->validated();
        $files = $validated['files'] ?? [];

        $merchant = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($merchant)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $warehouse_stock = $validated['warehouses'];
        foreach ($warehouse_stock as $ws) {
            if ($merchant->warehouses()->where('id', $ws['id'])->exists() == false) {
                return $this->error('Invalid warehouse id: ' . $ws['id'], 499);
            }
        }

        $product = new Product;
        $product->fill([
            'merchant_id' => $merchant->id,
            'name' => $validated['name'],
            'sku' => $this->generate_product_sku($merchant),
            'description' => $validated['description'],
            'currency' => $validated['currency'] ?? 'PHP',
            'price' => $validated['price'],
            'on_demand' => $validated['on_demand'],
            'product_condition_id' => ProductCondition::where('slug', $validated['condition'])->first()->id,
            'product_category_id' => ProductCategory::where('slug', $validated['category'])->first()->id,
        ]);

        $details = new ProductDetail;
        $details->fill([
            'width' => $validated['width'],
            'height' => $validated['height'],
            'length' => $validated['length'],
            'weight' => $validated['weight'],
            'mass_unit' => $validated['mass_unit'],
            'length_unit' => $validated['length_unit'],
        ]);

        try {
            DB::transaction(function () use ($product, $details, $warehouse_stock, $files) {
                $product->save();
                $details->product_id = $product->id;
                $details->save();
                foreach ($warehouse_stock as $ws) {
                    $product->warehouses()->syncWithPivotValues($ws['id'], ['stocks' => $ws['stock']], false);
                }

                foreach ($files as $key => $file) {
                    $media = $this->upload_file_media($product, $file, 'product_images');
                    $media->order_column = $key;
                    $media->save();
                }
            });

            $product->warehouses = $product->warehouses()->with('location')->get();
            $product->rating = 0;
            $product->reviews = 0;
            $product->sold_count = 0;
            $product->is_mine = true;
            $this->add_model_images($product, 'product_images');

            return $this->success($product);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     * Summary of update
     * @param \App\Http\Requests\Product\UpdateProductInfoRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     * 
     * @TODO: add on_demand
     */
    public function update(UpdateProductInfoRequest $request)
    {
        $validated = $request->validated();
        $sku = $validated['sku'];

        $name = $validated['name'] ?? null;
        $description = $validated['description'] ?? null;
        $price = $validated['price'] ?? null;
        $condition = $validated['condition'] ?? null;

        $width = $validated['width'] ?? null;
        $height = $validated['height'] ?? null;
        $length = $validated['length'] ?? null;
        $weight = $validated['weight'] ?? null;
        $mass_unit = $validated['mass_unit'] ?? null;
        $length_unit = $validated['length_unit'] ?? null;

        $merchant = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($merchant)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $product = $merchant->owned_products()->where('sku', $sku)->first();
        if (empty($product)) {
            return $this->error(config('Invalid product SKU.'), 499);
        }

        $product->fill([
            'name' => empty($name) ? $product->name : $name,
            'description' => empty($description) ? $product->description : $description,
            'price' => empty($price) ? $product->price : $price,
        ]);

        if (empty($condition) == false) {
            $condition = ProductCondition::where('slug', $condition)->first();
            $product->product_condition_id = $condition->id;
        }

        $detail = $product->productDetail;
        $detail->fill([
            'width' => empty($width) ? $detail->width : $width,
            'height' => empty($height) ? $detail->height : $height,
            'length' => empty($length) ? $detail->length : $length,
            'weight' => empty($weight) ? $detail->weight : $weight,
            'length_unit' => empty($length_unit) ? $detail->length_unit : $length_unit,
            'mass_unit' => empty($mass_unit) ? $detail->mass_unit : $mass_unit,
        ]);


        DB::beginTransaction();
        try {
            $product->save();
            $detail->save();
            DB::commit();

            $product->load('condition');
            $product->setHidden([
                'variations',
                'currency',
                'stock_count',
                'sale_amount',
                'sold_count',
                'product_condition_id',
                'product_category_id',
                'on_demand',
                'is_featured',
                'is_active',
                'approval_status',
                'created_at',
                'updated_at',
            ]);
            return $this->success($product);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }

    /**
     * Get details of product by SKU.
     * 
     * @param \App\Http\Requests\Product\ProductDetailsRequest $request
     * @return void
     */
    public function details(ProductDetailsRequest $request)
    {
        $validated = $request->validated();
        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $product = Product::select(
            'id',
            'sku',
            'name',
            'description',
            'price',
            'product_condition_id',
            'product_category_id',
            'merchant_id',
            'is_featured',
            'is_active',
            'on_demand',
            'approval_status as status',
        )
            ->where('sku', $validated['sku'])
            ->with([
                'condition',
                'productDetail',
                'category.parent_category',
                'warehouses' => function ($q) {
                    $q->select('warehouses.id', 'name');
                    $q->with(['location', 'availabilities']);
                },
                'merchant' => function ($q) {
                    $q->select('id', 'name', 'account_number');
                    $q->withAvg('reviews as rating', 'rating')->withCount('reviews as reviews');
                },
            ])
            ->first();

        $product->stock_count = $product->warehouses->sum('pivot.stocks');
        $product->is_mine = $this->is_mine($product, $entity);

        if ($product->is_mine) {
            $product->can_review = false;
        } else {
            $product->can_review = $product->reviews()->where([
                'reviewer_id' => $entity->id,
                'reviewer_type' => get_class($entity)
            ])->exists() == false;
        }
        foreach ($product->warehouses as $warehouse) {
            $warehouse->stocks = $warehouse->pivot->stocks;
        }

        $this->add_model_images($product, 'product_images');
        return $this->success($product);
    }

    /**
     * Summary of active_categories
     * @param \App\Http\Requests\Product\ProductActiveCategoriesRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function active_categories(ProductActiveCategoriesRequest $request)
    {
        $validated = $request->validated();
        $merchant = Merchant::where('account_number', $validated['merc_ac'])->first();


        $categories = ProductCategory::where('parent', null)
            ->whereHas('sub_categories', function ($q) use ($merchant) {
                $q->whereHas('products', function ($q) use ($merchant) {
                    $q->where([
                        'is_active' => 1,
                        'merchant_id' => $merchant->id,
                        'approval_status' => 'approved',
                    ]);
                });
            })->with([
                    'sub_categories' => function ($q) use ($merchant) {
                        $q->whereHas('products', function ($q) use ($merchant) {
                            $q->where([
                                'is_active' => 1,
                                'merchant_id' => $merchant->id,
                                'approval_status' => 'approved',
                            ]);
                        });
                    }
                ])->get();

        return $this->success($categories);
    }
}