<?php

namespace App\Merchant\SellerCenter\StoreManagement;

use App\Models\Merchant;
use App\Models\MerchantDetail;
use App\Models\Service;
use App\Models\Warehouse;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithImage;
use App\Traits\WithImageUploading;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MerchantSellerCenterStoreManagement extends Component
{
    use WithCustomPaginationLinks, WithFileUploads, WithImageUploading, WithPagination, WithImage;

    public $feature_type = 'services';

    public Merchant $merchant;

    public MerchantDetail $merchant_details;

    public $featuredAbout = false;

    public $latitude = 14.4258817;

    public $longitude = 121.0230479;

    public $photo_file;

    public $banner_file;

    public $banner = [];

    #[Locked]
    public $delete_images = [];

    public $description = '';

    public $description_banner_file;


    protected $listeners = [
        'locationInitialized' => 'location_initialized',
        'featuredProductsUpdated' => 'refreshFeaturedProducts',
        'featuredServicesUpdated' => 'refreshFeaturedServices',
    ];

    public $ranges = [
        'min_price' => 0,
        'max_price' => 50000,
        'min_stock' => 0,
        'max_stock' => 50000,
    ];

    protected $rules = [
        'store_details.description' => 'nullable',
    ];

    #[Locked]
    public $featured_products_currentPageNumber = 1;

    #[Locked]
    public $featured_services_currentPageNumber = 1;

    #[Locked]
    public $products_currentPageNumber = 1;

    #[Locked]
    public $services_currentPageNumber = 1;

    public $featured_products_hasPages = false;

    public $featured_services_hasPages = false;

    public $products_hasPages = false;

    public $services_hasPages = false;

    public $featured_products_totalPages = 1;

    public $featured_services_totalPages = 1;

    public $products_totalPages = 1;

    public $services_totalPages = 1;

    public $showServicesList = false;

    public $showProductsList = false;

    #[Locked]
    public $can_update = false;

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant->load('details');
        // Create details if the merchant doesn't have one yet.
        $this->merchant_details = $merchant->details;
        $this->description = $this->merchant_details->description;
        $banner = $this->merchant_details->getMedia('description_banner')->first();

        $this->banner = [];

        if ($banner) {
            $this->banner[] = [
                'name' => $banner->name,
                'image' => $this->get_media_url($banner),
                'size' => $banner->human_readable_size,
                'id' => $banner->id,
                'order' => $banner->order_column,
            ];
        }


        if (Gate::allows('merchant-store-management', [$this->merchant, 'update'])) {
            $this->can_update = true;
        }
    }

    #[Computed(persist: true)]
    public function merchant_rating()
    {
        $merchant_rating = floor($this->merchant->received_reviews()->avg('rating') * 10 + 0.5) / 10;

        return $merchant_rating ?? 0;
    }

    public function refreshFeaturedProducts()
    {
        $this->featured_products_currentPageNumber = 1;
        $this->featured_products();

        session()->flash('success', 'Featured Products updated successfully.');
    }

    public function refreshFeaturedServices()
    {
        $this->featured_services_currentPageNumber = 1;
        $this->featured_services();

        session()->flash('success', 'Featured Services updated successfully.');
    }

    public function location_initialized()
    {
        // Define the common distance calculation
        $distanceCalculation = '(6371000 * acos(cos(radians(?)) *
            cos(radians(locations.latitude)) *
            cos(radians(locations.longitude) - radians(?)) +
            sin(radians(?)) *
            sin(radians(locations.latitude))
        )) AS distance';

        // Fetch services and warehouses with the common distance calculation
        $services = $this->merchant->owned_services()
            ->selectRaw('services.id, services.name, locations.latitude, locations.longitude, ' . $distanceCalculation, [$this->latitude, $this->longitude, $this->latitude])
            ->join('locations', function ($join) {
                $join->on('services.id', '=', 'locations.entity_id')
                    ->where('locations.entity_type', '=', Service::class);
            })
            ->having('distance', '<', 5000)
            ->get();

        $warehouses = $this->merchant->warehouses()
            ->selectRaw('warehouses.id, warehouses.name, locations.latitude, locations.longitude, ' . $distanceCalculation, [$this->latitude, $this->longitude, $this->latitude])
            ->join('locations', function ($join) {
                $join->on('warehouses.id', '=', 'locations.entity_id')
                    ->where('locations.entity_type', '=', Warehouse::class);
            })
            ->having('distance', '<', 5000)
            ->get();

        // Dispatch the event with the collected data
        $this->dispatch('markers_initialized', [
            'services' => $services->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'latitude' => $service->latitude,
                    'longitude' => $service->longitude,
                ];
            })->toArray(),
            'warehouses' => $warehouses->map(function ($warehouse) {
                return [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name,
                    'latitude' => $warehouse->latitude,
                    'longitude' => $warehouse->longitude,
                ];
            })->toArray(),
        ]);
    }

    #[On('updateDescriptionBanner')]
    public function updateDescriptionBanner($images)
    {
        $this->banner = $images;

        foreach ($this->banner as $key => $image) {
            $this->banner[$key]['image'] = new TemporaryUploadedFile($image['image'], config('filesystems.default'));
        }
    }

    #[On('updateDeletedImages')]
    public function updateDeletedImages($images)
    {
        $this->delete_images = $images;
    }

    // save changes for 'About Store' edits
    public function submitEdit()
    {
        $rules = [
            'banner' => 'array|max:1',
            'banner.*' => 'array:id,name,image,size,order',
            'banner.*.id' => 'nullable',
            'banner.*.name' => 'nullable',
            'banner.*.size' => 'nullable',
            'banner.*.order' => 'nullable',
            'description' => 'nullable|string|max:1200',
        ];

        if (!empty($this->banner) && isset($this->banner[0]['image']) && $this->banner[0]['id'] === null) {
            $rules['banner.*.image'] = 'required|image|mimes:png,jpg,jpeg|max:5120';
        }

        $this->validate($rules);

        $this->merchant_details->description = $this->description;

        DB::beginTransaction();
        try {
            $this->merchant_details->save();

            foreach ($this->delete_images as $image) {
                if ($image['id'] !== null) {
                    $media = Media::find($image['id']);
                    $media->delete();
                }
            }

            foreach ($this->banner as $image) {
                if ($image['id'] !== null) {
                    continue;
                }
                $this->upload_file_media($this->merchant_details, $image['image'], 'description_banner');
            }

            DB::commit();
            session()->flash('success', 'About Store updated successfully.');
            return $this->redirect(route('merchant.seller-center.store-management', ['merchant' => $this->merchant]));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MerchantSellerCenterAboutStore.save: ' . $e->getMessage());
            return session()->flash('error', 'Something went wrong. Please try again later.');
        }
    }

    // pagination for featured products, features services, products list, services list
    public function handlePageArrow($section, $direction)
    {
        if (! in_array($direction, ['left', 'right']) || ! in_array($section, ['fp', 'p', 'fs', 's'])) {
            return;
        }

        $pageMap = [
            'fp' => ['current' => 'featured_products_currentPageNumber', 'total' => 'featured_products_totalPages'],
            'p' => ['current' => 'products_currentPageNumber', 'total' => 'products_totalPages'],
            'fs' => ['current' => 'featured_services_currentPageNumber', 'total' => 'featured_services_totalPages'],
            's' => ['current' => 'services_currentPageNumber', 'total' => 'services_totalPages'],
        ];

        if (! isset($pageMap[$section])) {
            return;
        }

        $currentPageProperty = $pageMap[$section]['current'];
        $totalPagesProperty = $pageMap[$section]['total'];

        if ($direction === 'left') {
            $this->$currentPageProperty = max(1, $this->$currentPageProperty - 1);
        } elseif ($direction === 'right') {
            $this->$currentPageProperty = min($this->$totalPagesProperty, $this->$currentPageProperty + 1);
        }
    }

    // save changes for merchant logo and banner edits
    public function save()
    {
        DB::beginTransaction();
        try {
            if (! empty($this->photo_file)) {
                $this->merchant->clearMediaCollection('merchant_logo');
    
                $media = $this->upload_file_media($this->merchant, $this->photo_file, 'merchant_logo');
            }
    
            if (! empty($this->banner_file)) {
                $this->merchant_details->clearMediaCollection('merchant_banner');
    
                $this->upload_file_media($this->merchant_details, $this->banner_file, 'merchant_banner');
            }
    
            $this->merchant_details->save();

            DB::commit();
    
            $this->banner_file = null;
            $this->photo_file = null;
            session()->flash('success', 'Store details updated!');

            return $this->redirect(route('merchant.seller-center.store-management', ['merchant' => $this->merchant]));
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('MerchantSellerCenterStore.save: ' . $ex->getMessage());
            return session()->flash('error', 'Something went wrong. Please try again later.');
        }

    }

    #[Computed]
    public function featured_products()
    {
        $featured_products = $this->merchant->owned_products()
            ->with(['first_image', 'condition'])
            ->where('is_featured', true)
            ->withCount('reviews')
            ->withAvg('reviews as rating', 'rating')
            ->orderBy('updated_at')
            ->paginate(5, ['*'], 'page', $this->featured_products_currentPageNumber);

        $this->featured_products_hasPages = $featured_products->hasPages();
        $this->featured_products_totalPages = $featured_products->lastPage();

        return $featured_products;
    }

    #[Computed]
    public function featured_services()
    {
        $featured_services = $this->merchant->owned_services()
            ->with(['first_image', 'location'])
            ->where('is_featured', true)
            ->withCount('reviews')
            ->withAvg('reviews as rating', 'rating')
            ->orderBy('updated_at')
            ->paginate(5, ['*'], 'page', $this->featured_services_currentPageNumber);

        $this->featured_services_hasPages = $featured_services->hasPages();
        $this->featured_services_totalPages = $featured_services->lastPage();

        return $featured_services;
    }

    #[Computed]
    public function products()
    {

        $products = $this->merchant->owned_products()
            ->with(['first_image', 'condition'])
            ->withCount('reviews')
            ->withAvg('reviews as rating', 'rating')
            ->orderBy('updated_at')
            ->paginate(5, ['*'], 'page', $this->products_currentPageNumber);

        $this->products_hasPages = $products->hasPages();
        $this->products_totalPages = $products->lastPage();

        return $products;
    }

    #[Computed]
    public function services()
    {
        $services = $this->merchant->owned_services()
            ->with(['first_image', 'location'])
            ->withCount('reviews')
            ->withAvg('reviews as rating', 'rating')
            ->orderBy('updated_at')
            ->paginate(5, ['*'], 'page', $this->services_currentPageNumber);

        $this->services_hasPages = $services->hasPages();
        $this->services_totalPages = $services->lastPage();

        return $services;
    }

    public function loadServicesList()
    {
        $this->showServicesList = true;
    }

    public function loadProductsList()
    {
        $this->showProductsList = true;
    }

    #[Layout('layouts.merchant.seller-center')]
    public function render()
    {
        return view('merchant.seller-center.store-management.merchant-seller-center-store-management');
    }
}
