<?php

namespace App\Merchant\SellerCenter\Dashboard;

use App\Models\Booking;
use App\Models\BookingStatus;
use App\Models\Merchant;
use App\Models\ShippingStatus;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SellerCenterBusinessInsights extends Component
{
    public Merchant $merchant;
    public string $dateFilter = 'past_year';
    public string $dateLabel = 'month';
    public string $business_insights_tab = 'products';
    public string $product_insights_tab = 'sales';
    public string $service_insights_tab = 'sales';
    public $product_sales = [];
    public $product_orders = [];
    public $service_sales = [];
    public $service_bookings = [];
    public $service_inquiries = [];

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;
        $this->updateDataComparison();
        $this->updateChart();
    }

    public function updatedDateFilter()
    {
        if (!in_array($this->dateFilter, ['past_24_hours', 'past_week', 'past_30_days', 'past_6_months', 'past_year'])) {
            $this->dateFilter = 'past_year';
        }

        $this->updateDataComparison();
        $this->updateChart();
    }

    public function updatedBusinessInsightsTab()
    {
        if (!in_array($this->business_insights_tab, ['products', 'services'])) {
            $this->business_insights_tab = 'products';
        }

        $this->updateChart();
    }

    public function updatedProductInsightsTab()
    {
        if (!in_array($this->product_insights_tab, ['sales', 'orders'])) {
            $this->product_insights_tab = 'sales';
        }

        $this->updateChart();
    }

    public function updatedServiceInsightsTab()
    {
        if (!in_array($this->service_insights_tab, ['sales', 'bookings', 'inquiries'])) {
            $this->service_insights_tab = 'sales';
        }

        $this->updateChart();
    }

    #[Computed(persist: true)]
    public function get_date_range()
    {
        return [
            'present' => [
                'past_24_hours' => [Carbon::now()->subDay(), Carbon::now()],
                'past_week' => [Carbon::now()->subWeek(), Carbon::now()],
                'past_30_days' => [Carbon::now()->subDays(30), Carbon::now()],
                'past_6_months' => [Carbon::now()->subMonths(6), Carbon::now()],
                'past_year' => [Carbon::now()->subYear(), Carbon::now()],
            ],
            'previous' => [
                'past_24_hours' => [Carbon::now()->subDay()->subDay(), Carbon::now()->subDay()],
                'past_week' => [Carbon::now()->subWeek()->subWeek(), Carbon::now()->subWeek()],
                'past_30_days' => [Carbon::now()->subDays(30)->subDays(30), Carbon::now()->subDays(30)],
                'past_6_months' => [Carbon::now()->subMonths(6)->subMonths(6), Carbon::now()->subMonths(6)],
                'past_year' => [Carbon::now()->subYear()->subYear(), Carbon::now()->subYear()],
            ]
        ];
    }

    public function get_date_format()
    {
        $format = 'Y-m-d H:i:s';
        switch ($this->dateFilter) {
            case 'past_24_hours':
                $format = 'H';
                $this->dateLabel = '24 hours';
                break;
            case 'past_week':
                $format = 'l';
                $this->dateLabel = 'week';
                break;
            case 'past_30_days':
                $format = 'M d';
                $this->dateLabel = '30 days';
                break;
            case 'past_6_months':
                $format = 'F';
                $this->dateLabel = '6 months';
                break;
            case 'past_year':
                $format = 'Y-m';
                $this->dateLabel = 'year';
                break;
        }

        return $format;
    }

    public function get_date_labels()
    {
        $labels = [];
        switch ($this->dateFilter) {
            case 'past_24_hours':
                $now = Carbon::now('Asia/Manila');

                for ($i = 23; $i >= 0; $i--) {
                    if ($i === 0) {
                        $labels[] = 'now';
                        continue;
                    }

                    // $startTime = $now->copy()->subHours($i);

                    // $hoursPassed = $startTime->diffInHours($now);

                    $labels[] = $i . 'h';
                }
                break;
            case 'past_week':
                $now = Carbon::now('Asia/Manila');
                for ($i = 6; $i >= 0; $i--) {
                    $labels[] = $now->copy()->subDays($i)->format('l');
                }
                break;
            case 'past_30_days':
                $now = Carbon::now('Asia/Manila');
                for ($i = 29; $i >= 0; $i--) {
                    $labels[] = $now->copy()->subDays($i)->format('M d');
                }
                break;
            case 'past_6_months':
                $now = Carbon::now('Asia/Manila');
                for ($i = 5; $i >= 0; $i--) {
                    $labels[] = $now->copy()->subMonths($i)->format('F');
                }
                break;
            case 'past_year':
                $now = Carbon::now('Asia/Manila');
                for ($i = 11; $i >= 0; $i--) {
                    $labels[] = $now->copy()->subMonths($i)->format('Y-m');
                }
                break;
        }

        return $labels;
    }

    #[Computed(persist: true)]
    public function shipping_statuses()
    {
        return ShippingStatus::all();
    }

    #[Computed(persist: true)]
    public function booking_statuses()
    {
        return BookingStatus::all();
    }

    public function updateDataComparison()
    {
        $date = $this->get_date_range();

        $status_completed = $this->shipping_statuses->where('slug', 'completed')->first()->id;
        $product_sales = $this->merchant->orders_through_products()
            ->whereBetween('product_orders.created_at', [$date['present'][$this->dateFilter][0], $date['present'][$this->dateFilter][1]])
            ->where('shipping_status_id', $status_completed)
            ->selectRaw('coalesce(sum(amount * quantity), 0) as sum_amount')
            ->value('sum_amount');
        $vs_previous_product_sales = $this->merchant->orders_through_products()
            ->whereBetween('product_orders.created_at', [$date['previous'][$this->dateFilter][0], $date['previous'][$this->dateFilter][1]])
            ->where('shipping_status_id', $status_completed)
            ->selectRaw('coalesce(sum(amount * quantity), 0) as sum_amount')
            ->value('sum_amount');
        $difference = $product_sales - $vs_previous_product_sales;
        $this->product_sales = [
            'present' => $product_sales,
            'vs_previous' => abs($difference),
            'positive' => $difference >= 0 ? true : false,
        ];

        $product_orders = $this->merchant->orders_through_products()
            ->whereBetween('product_orders.created_at', [$date['present'][$this->dateFilter][0], $date['present'][$this->dateFilter][1]])
            ->count();
        $vs_previous_product_orders = $this->merchant->orders_through_products()
            ->whereBetween('product_orders.created_at', [$date['previous'][$this->dateFilter][0], $date['previous'][$this->dateFilter][1]])
            ->count();
        $difference = $product_orders - $vs_previous_product_orders;
        $this->product_orders = [
            'present' => $product_orders,
            'vs_previous' => abs($difference),
            'positive' => $difference >= 0 ? true : false,
        ];

        $status_fulfilled = $this->booking_statuses->where('slug', 'fulfilled')->first()->id;
        $service_sales = Booking::whereHas('service', function ($service) {
            $service->where('merchant_id', $this->merchant->id);
        })
            ->withSum('transactions', 'amount')
            ->whereHas('invoice', function ($invoice) {
                $invoice->where('status', 'paid');
            })
            ->whereBetween('bookings.created_at', [$date['present'][$this->dateFilter][0], $date['present'][$this->dateFilter][1]])
            ->where('booking_status_id', $status_fulfilled)
            ->get(['id', 'transactions_sum_amount'])
            ->sum('transactions_sum_amount');
        $vs_previous_service_sales = Booking::whereHas('service', function ($service) {
            $service->where('merchant_id', $this->merchant->id);
        })
            ->withSum('transactions', 'amount')
            ->whereHas('invoice', function ($invoice) {
                $invoice->where('status', 'paid');
            })
            ->whereBetween('bookings.created_at', [$date['previous'][$this->dateFilter][0], $date['previous'][$this->dateFilter][1]])
            ->where('booking_status_id', $status_fulfilled)
            ->get(['id', 'transactions_sum_amount'])
            ->sum('transactions_sum_amount');
        $difference = $service_sales - $vs_previous_service_sales;
        $this->service_sales = [
            'present' => $service_sales,
            'vs_previous' => abs($difference),
            'positive' => $difference >= 0 ? true : false,
        ];

        $status_bookings = $this->booking_statuses->whereIn('slug', ['booked', 'in_progress', 'fulfilled'])->pluck('id')->toArray();
        $service_bookings = $this->merchant->bookings_through_services()->whereBetween('bookings.created_at', [$date['present'][$this->dateFilter][0], $date['present'][$this->dateFilter][1]])
            ->whereIn('bookings.booking_status_id', $status_bookings)
            ->count();
        $vs_previous_service_bookings = $this->merchant->bookings_through_services()->whereBetween('bookings.created_at', [$date['previous'][$this->dateFilter][0], $date['previous'][$this->dateFilter][1]])
            ->whereIn('bookings.booking_status_id', $status_bookings)
            ->count();
        $difference = $service_bookings - $vs_previous_service_bookings;
        $this->service_bookings = [
            'present' => $service_bookings,
            'vs_previous' => abs($difference),
            'positive' => $difference >= 0 ? true : false,
        ];

        $status_inquiry = $this->booking_statuses->whereIn('slug', ['inquiry', 'quoted'])->pluck('id')->toArray();
        $service_inquiries = $this->merchant->bookings_through_services()->whereBetween('bookings.created_at', [$date['present'][$this->dateFilter][0], $date['present'][$this->dateFilter][1]])
            ->whereIn('bookings.booking_status_id', $status_inquiry)
            ->count();
        $vs_previous_service_inquiries = $this->merchant->bookings_through_services()->whereBetween('bookings.created_at', [$date['previous'][$this->dateFilter][0], $date['previous'][$this->dateFilter][1]])
            ->whereIn('bookings.booking_status_id', $status_inquiry)
            ->count();
        $difference = $service_inquiries - $vs_previous_service_inquiries;
        $this->service_inquiries = [
            'present' => $service_inquiries,
            'vs_previous' => abs($difference),
            'positive' => $difference >= 0 ? true : false,
        ];
    }

    public function updateChart()
    {
        $data = [];
        if ($this->business_insights_tab == 'products') {
            if ($this->product_insights_tab === 'sales') {
                $data = $this->updateProductSalesChart();
            } elseif ($this->product_insights_tab === 'orders') {
                $data = $this->updateProductOrdersChart();
            }
        } elseif ($this->business_insights_tab == 'services') {
            if ($this->service_insights_tab == 'sales') {
                $data = $this->updateServiceSalesChart();
            } elseif ($this->service_insights_tab == 'bookings') {
                $data = $this->updateServiceBookingsChart();
            } elseif ($this->service_insights_tab == 'inquiries') {
                $data = $this->updateServiceInquiriesChart();
            }
        }

        $this->dispatch('update-chart', $data);
    }

    private function get_chart_date_range()
    {
        return [
            'past_24_hours' => array_map(function ($hour) {
                return [
                    Carbon::now()->subHours($hour)->startOfHour(),
                    Carbon::now()->subHours($hour)->endOfHour(),
                ];
            }, range(23, 0, -1)),
            'past_week' => array_map(function ($day) {
                return [
                    Carbon::now()->subDays($day)->startOfDay(),
                    Carbon::now()->subDays($day)->endOfDay(),
                ];
            }, range(6, 0, -1)),
            'past_30_days' => array_map(function ($day) {
                return [
                    Carbon::now()->subDays($day)->startOfDay(),
                    Carbon::now()->subDays($day)->endOfDay(),
                ];
            }, range(29, 0, -1)),
            'past_6_months' => array_map(function ($month) {
                return [
                    Carbon::now()->subMonths($month)->startOfMonth(),
                    Carbon::now()->subMonths($month)->endOfMonth(),
                ];
            }, range(5, 0, -1)),
            'past_year' => array_map(function ($month) {
                return [
                    Carbon::now()->subMonths($month)->startOfMonth(),
                    Carbon::now()->subMonths($month)->endOfMonth(),
                ];
            }, range(11, 0, -1)),
        ];
    }

    public function updateProductSalesChart()
    {
        $date = $this->get_chart_date_range();
        $format = $this->get_date_format();
        $labels = $this->get_date_labels();
        $completed_status = $this->shipping_statuses->where('slug', 'completed')->first()->id;

        $infos = [];

        foreach ($date[$this->dateFilter] as $range) {
            $infos[] = $this->merchant->orders_through_products()
                ->whereBetween('product_orders.created_at', [$range[0], $range[1]])
                ->where('product_orders.shipping_status_id', $completed_status)
                ->selectRaw('coalesce(sum(product_orders.amount * product_orders.quantity), 0) as sum_amount')
                ->value('sum_amount');
        }

        $infos = array_combine($labels, array_values($infos));

        return [
            'labels' => $labels,
            'infos' => $infos,
            'data_label' => 'Product Sales',
        ];
    }

    public function updateProductOrdersChart()
    {
        $date = $this->get_chart_date_range();
        $format = $this->get_date_format();
        $labels = $this->get_date_labels();

        $infos = [];

        foreach ($date[$this->dateFilter] as $range) {
            $infos[] = $this->merchant->orders_through_products()
                ->whereBetween('product_orders.created_at', [$range[0], $range[1]])
                ->count();
        }

        $infos = array_combine($labels, array_values($infos));

        return [
            'labels' => $labels,
            'infos' => $infos,
            'data_label' => 'Product Orders',
        ];
    }

    public function updateServiceSalesChart()
    {
        $date = $this->get_chart_date_range();
        $format = $this->get_date_format();
        $labels = $this->get_date_labels();
        $status_fulfilled = $this->booking_statuses->where('slug', str('Fulfilled')->slug())->first()->id;

        $infos = [];

        foreach ($date[$this->dateFilter] as $range) {
            $infos[] = $this->merchant->bookings_through_services()
                ->whereBetween('bookings.created_at', [$range[0], $range[1]])
                ->where('bookings.booking_status_id', $status_fulfilled)
                ->whereHas('transactions')
                ->withSum('transactions as sum_amount', 'amount')
                ->whereHas('invoice', function ($invoice) {
                    $invoice->where('status', 'paid');
                })
                ->get()
                ->sum('sum_amount');
        }

        $infos = array_combine($labels, array_values($infos));

        return [
            'labels' => $labels,
            'infos' => $infos,
            'data_label' => 'Service Sales',
        ];
    }

    public function updateServiceBookingsChart()
    {
        $date = $this->get_chart_date_range();
        $format = $this->get_date_format();
        $labels = $this->get_date_labels();
        $status_bookings = $this->booking_statuses->whereIn('slug', ['booked', 'in_progress', 'fulfilled'])->pluck('id')->toArray();

        $infos = [];

        foreach ($date[$this->dateFilter] as $range) {
            $infos[] = $this->merchant->bookings_through_services()
                ->whereBetween('bookings.created_at', [$range[0], $range[1]])
                ->whereIn('bookings.booking_status_id', $status_bookings)
                ->count();
        }

        $infos = array_combine($labels, array_values($infos));

        return [
            'labels' => $labels,
            'infos' => $infos,
            'data_label' => 'Service Bookings',
        ];
    }

    public function updateServiceInquiriesChart()
    {
        $date = $this->get_chart_date_range();
        $format = $this->get_date_format();
        $labels = $this->get_date_labels();
        $status_inquiries = $this->booking_statuses->whereIn('slug', ['inquiry', 'quoted'])->pluck('id')->toArray();

        $infos = [];

        foreach ($date[$this->dateFilter] as $range) {
            $infos[] = $this->merchant->bookings_through_services()
                ->whereBetween('bookings.created_at', [$range[0], $range[1]])
                ->whereIn('bookings.booking_status_id', $status_inquiries)
                ->count();
        }

        $infos = array_combine($labels, array_values($infos));

        return [
            'labels' => $labels,
            'infos' => $infos,
            'data_label' => 'Service Inquiries',
        ];
    }

    public function render()
    {
        return view('merchant.seller-center.dashboard.seller-center-business-insights');
    }
}
