<?php

namespace App\Merchant\SellerCenter\Dashboard;

use App\Models\Booking;
use App\Models\BookingStatus;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\ReturnOrderStatus;
use App\Models\Service;
use App\Models\ShippingStatus;
use App\Models\User;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithImage;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class MerchantSellerCenterDashboard extends Component
{
    use WithPagination, WithCustomPaginationLinks, WithImage;

    public Merchant $merchant;

    public $active_requests_tab = 'orders';
    public $sortBy = 'amount', $sortDirection = 'asc', $sort;

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;
    }

    #[Computed]
    public function shipping_statuses()
    {
        return ShippingStatus::select('id', 'slug')->get();
    }

    #[Computed]
    public function booking_statuses()
    {
        return BookingStatus::get(['id', 'slug']);
    }

    #[Computed(persist: true)]
    public function count_pending_orders()
    {
        $pending = $this->shipping_statuses->whereIn('slug', ['pending', 'packed'])->pluck('id')->toArray();
        return $this->merchant->orders_through_products()->whereIn('shipping_status_id', $pending)->count();
    }

    #[Computed(persist: true)]
    public function count_shipping_products()
    {
        $shipping = $this->shipping_statuses->where('slug', 'shipping')->first()->id;
        return $this->merchant->orders_through_products()->where('shipping_status_id', $shipping)->count();
    }

    #[Computed(persist: true)]
    public function count_sold_out_products()
    {
        return $this->merchant->owned_products()->active()->where('stock_count', 0)->count();
    }

    #[Computed(persist: true)]
    public function count_pending_bookings()
    {
        $booked = $this->booking_statuses->where('slug', 'booked')->first()->id;
        return $this->merchant->bookings_through_services()->where('booking_status_id', $booked)->count();
    }

    #[Computed(persist: true)]
    public function count_to_ship_products()
    {
        $to_ship = $this->shipping_statuses->where('slug', 'to_ship')->first()->id;
        return $this->merchant->orders_through_products()->where('shipping_status_id', $to_ship)->count();
    }

    #[Computed(persist: true)]
    public function count_pending_returns()
    {
        $pending_returns = ReturnOrderStatus::whereIn('slug', ['pending_return', 'return_initiated'])->pluck('id')->toArray();
        return $this->merchant->return_orders_through_products()->whereIn('return_order_status_id', $pending_returns)->count();
    }

    #[Computed(persist: true)]
    public function count_pending_inquiries()
    {
        $inquiry = $this->booking_statuses->where('slug', 'inquiry')->first()->id;
        return $this->merchant->bookings_through_services()->where('booking_status_id', $inquiry)->count();
    }

    #[Computed(persist: true)]
    public function count_in_progress_bookings()
    {
        $in_progress = $this->booking_statuses->where('slug', 'in_progress')->first()->id;
        return $this->merchant->bookings_through_services()->where('booking_status_id', $in_progress)->count();
    }

    #[Computed]
    public function best_selling_products()
    {
        $completed = $this->shipping_statuses->where('slug', 'completed')->first()->id;
        $best_selling_products = $this->merchant->owned_products()
            ->whereHas('orders', function ($query) use ($completed) {
                $query->where('shipping_status_id', $completed);
            })
            ->select('id', 'name')
            ->withCount('orders as sold_count')
            ->orderBy('sold_count', 'desc')
            ->orderBy('name', 'asc')
            ->limit(5)
            ->get();

        return $best_selling_products;
    }

    #[Computed]
    public function best_selling_services()
    {
        $fulfilled = $this->booking_statuses->where('slug', 'fulfilled')->first()->id;
        $best_selling_services = $this->merchant->owned_services()
            ->whereHas('bookings', function ($query) use ($fulfilled) {
                $query->where('booking_status_id', $fulfilled);
            })
            ->select('id', 'name')
            ->withCount(['bookings as sold_count' => function ($query) use ($fulfilled) {
                $query->where('booking_status_id', $fulfilled);
            }])
            ->orderBy('sold_count', 'desc')
            ->orderBy('name', 'asc')
            ->limit(5)
            ->get();

        return $best_selling_services;
    }

    public function updatedActiveRequestsTab()
    {
        if ($this->active_requests_tab === 'orders') {
            $this->sortBy = 'amount';
        }

        $this->sortDirection = 'asc';
        $this->resetPage();
    }

    public function sortOrders($value)
    {
        if ($this->active_requests_tab !== 'orders') {
            return;
        }

        $sort = match ($value) {
            'amount' => 'amount',
            'delivery' => 'shipping_option_id',
            default => 'amount',
        };

        if ($this->sortBy === $sort) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $sort;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleBookingsSort()
    {
        if ($this->active_requests_tab !== 'bookings') {
            return;
        }

        $this->sortDirection = $this->sortDirection == 'asc' ? 'desc' : 'asc';
    }

    #[Layout('layouts.merchant.seller-center')]
    public function render()
    {
        if ($this->active_requests_tab === 'orders') {
            $active = $this->shipping_statuses->whereIn('slug', ['pending', 'packed'])->pluck('id')->toArray();
            $requests = $this->merchant->orders_through_products()
                ->whereIn('shipping_status_id', $active)
                ->with(['buyer' => function (MorphTo $query) {
                    $query->morphWith([
                        User::class => ['profile', 'media' => function ($q) {
                            $q->where('collection_name', 'profile_picture');
                        }],
                        Merchant::class => ['media' => function ($q) {
                            $q->where('collection_name', 'merchant_logo');
                        }],
                    ]);
                }, 'product:id,name', 'shipping_status', 'payment_option'])
                ->orderBy($this->sortBy, $this->sortDirection)
                ->paginate(6);
        } elseif ($this->active_requests_tab === 'bookings') {
            $active = $this->booking_statuses->whereIn('slug', ['booked', 'in_progress'])->pluck('id')->toArray();
            $requests = $this->merchant->bookings_through_services()
                ->whereIn('booking_status_id', $active)
                ->with(['entity' => function (MorphTo $query) {
                    $query->morphWith([
                        User::class => ['profile', 'media' => function ($q) {
                            $q->where('collection_name', 'profile_picture');
                        }],
                        Merchant::class => ['media' => function ($q) {
                            $q->where('collection_name', 'merchant_logo');
                        }],
                    ]);
                } , 'service:id,name', 'status'])
                ->orderBy('service_date', $this->sortDirection)
                ->paginate(6);
        } else {
            $requests = null;
        }

        $elements = $this->getPaginationElements($requests);

        return view('merchant.seller-center.dashboard.merchant-seller-center-dashboard', [
            'requests' => $requests,
            'elements' => $elements,
        ]);
    }
}
