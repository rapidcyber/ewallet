<?php

namespace App\Merchant\SellerCenter\Logistics\ReturnOrders;

use App\Models\Merchant;
use App\Models\ReturnOrderStatus;
use App\Models\User;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithImage;
use App\Traits\WithValidPhoneNumber;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class MerchantSellerCenterLogisticsReturnOrders extends Component
{
    use WithPagination, WithCustomPaginationLinks, WithImage, WithValidPhoneNumber;

    public Merchant $merchant;
    public ?string $activeBox = '';
    public $searchTerm = '';
    #[Locked]
    public $search_value = '';

    public $return_order_id = null;

    public $date;
    public $deadline;
    public $amount;
    public $delivery_type;

    public $showRefundModal = false;
    public $showReturnRefundModal = false;
    public $showRejectRequestModal = false;
    public $showRejectRequestAfterReturnModal = false;
    public $showRespondModal = false;
    public $showViewResponseModal = false;
    public $showViewResolutionModal = false;
    public $showLogisticsStatusModal = false;
    public $showProcessRefundModal = false;

    protected $allowed_date_options = [
        'today',
        'past_week',
        'past_month',
        'past_6_months',
        'past_year',
    ];

    protected $allowed_deadline_options = [
        '12',
        '24',
        '48',
        '72',
    ];

    protected $allowed_amount_options = [
        '0-4999',
        '5000-9999',
        '10000-14999',
        '15000+'
    ];

    protected $allowed_delivery_type_options = [
        'standard',
        'on_demand',
    ];

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;
    }

    #[Computed]
    public function status()
    {
        return ReturnOrderStatus::toBase()->get();
    }

    public function updatedActiveBox()
    {
        if (!in_array($this->activeBox, ['return_initiated', 'return_in_progress', 'rejected', 'dispute_in_progress', 'resolved'])) {
            $this->activeBox = '';
        }

        $this->resetPage();
    }

    public function open_modal($return_order_id, $modal_name)
    {
        if (!in_array($modal_name, ['refund', 'return_refund', 'reject_request', 'reject_request_after_return', 'respond', 'view_response', 'view_resolution', 'logistics_status', 'process_refund'])) {
            return session()->flash('error', 'Invalid action');
        }

        switch ($modal_name) {
            case 'refund':
                $this->showRefundModal = true;
                break;
            case 'return_refund':
                $this->showReturnRefundModal = true;
                break;
            case 'reject_request':
                $this->showRejectRequestModal = true;
                break;
            case 'reject_request_after_return':
                $this->showRejectRequestAfterReturnModal = true;
                break;
            case 'respond':
                $this->showRespondModal = true;
                break;
            case 'view_response':
                $this->showViewResponseModal = true;
                break;
            case 'view_resolution':
                $this->showViewResolutionModal = true;
                break;
            case 'logistics_status':
                $this->showLogisticsStatusModal = true;
                break;
            case 'process_refund':
                $this->showProcessRefundModal = true;
                break;
        }

        $return_order = $this->merchant->return_orders_through_products()->where('return_orders.id', $return_order_id)->first();

        if (!$return_order) {
            $this->reset([
                'return_order_id',
                'showRefundModal',
                'showReturnRefundModal',
                'showRejectRequestModal',
                'showRejectRequestAfterReturnModal',
                'showRespondModal',
                'showViewResponseModal',
                'showViewResolutionModal',
                'showLogisticsStatusModal',
                'showProcessRefundModal'
            ]);
            return session()->flash('error', 'Return Order Request not found');
        }

        $this->return_order_id = $return_order_id;
    }

    #[On('closeModal')]
    public function closeModal()
    {
        $this->reset([
            'return_order_id',
            'showRefundModal',
            'showReturnRefundModal',
            'showRejectRequestModal',
            'showRejectRequestAfterReturnModal',
            'showRespondModal',
            'showViewResponseModal',
            'showViewResolutionModal',
            'showLogisticsStatusModal',
            'showProcessRefundModal'
        ]);
    }

    #[Computed]
    public function count_all()
    {
        return $this->merchant->return_orders_through_products()->count();
    }

    #[Computed]
    public function count_initiated()
    {
        $status_initiated = $this->status->where('slug', 'return_initiated')->first();
        return $this->merchant->return_orders_through_products()->where('return_order_status_id', $status_initiated->id)->count();
    }

    #[Computed]
    public function count_in_progress()
    {
        $status_in_progress = $this->status->where('slug', 'return_in_progress')->first();
        $child_statuses = $this->status->where('parent', $status_in_progress->id)->pluck('id')->toArray();

        $statuses = array_merge([$status_in_progress->id], $child_statuses);
        return $this->merchant->return_orders_through_products()->whereIn('return_order_status_id', $statuses)->count();
    }

    #[Computed]
    public function count_rejected()
    {
        $status_rejected = $this->status->where('slug', 'rejected')->first();
        $child_statuses = $this->status->where('parent', $status_rejected->id)->pluck('id')->toArray();

        $statuses = array_merge([$status_rejected->id], $child_statuses);
        return $this->merchant->return_orders_through_products()->whereIn('return_order_status_id', $statuses)->count();
    }

    #[Computed]
    public function count_disputed()
    {
        $status_disputed = $this->status->where('slug', 'dispute_in_progress')->first();
        $child_statuses = $this->status->where('parent', $status_disputed->id)->pluck('id')->toArray();

        $statuses = array_merge([$status_disputed->id], $child_statuses);
        return $this->merchant->return_orders_through_products()->whereIn('return_order_status_id', $statuses)->count();
    }

    #[Computed]
    public function count_resolved()
    {
        $status_resolved = $this->status->where('slug', 'resolved')->first();
        $child_statuses = $this->status->where('parent', $status_resolved->id)->pluck('id')->toArray();

        $statuses = array_merge([$status_resolved->id], $child_statuses);
        return $this->merchant->return_orders_through_products()->whereIn('return_order_status_id', $statuses)->count();
    }

    public function search()
    {
        $this->search_value = $this->searchTerm;
        $this->resetPage();
    }

    public function reset_search()
    {
        $this->reset([
            'search_value',
            'searchTerm',
        ]);

        $this->resetPage();
    }

    public function view_return_order($return_order_id)
    {
        $return_order = $this->merchant->return_orders_through_products()
            ->where('return_orders.id', $return_order_id)
            ->first();

        if (!$return_order) {
            return session()->flash('error', 'Return Order Request not found');
        }

        return $this->redirect(route('merchant.seller-center.logistics.return-orders.show', ['merchant' => $this->merchant, 'returnOrder' => $return_order]));
    }

    public function calculate_remaining_hours($created_at)
    {
        $date = Carbon::parse($created_at);
        $target_time = $date->copy()->addHours(96.5);

        if ($target_time->lt(Carbon::now())) {
            return 'Expired';
        }

        return (int)$target_time->diffInHours(null, true) . ' hours left';
    }

    private function get_statuses()
    {
        switch ($this->activeBox) {
            case 'return_initiated':
                return $this->status->where('slug', 'return_initiated')->pluck('id')->toArray();
            case 'return_in_progress':
                $status_in_progress = $this->status->where('slug', 'return_in_progress')->first();
                $child_statuses = $this->status->where('parent', $status_in_progress->id)->pluck('id')->toArray();

                return array_merge([$status_in_progress->id], $child_statuses);
            case 'rejected':
                $status_rejected = $this->status->where('slug', 'rejected')->first();
                $child_statuses = $this->status->where('parent', $status_rejected->id)->pluck('id')->toArray();

                return array_merge([$status_rejected->id], $child_statuses);
            case 'dispute_in_progress':
                $status_disputed = $this->status->where('slug', 'dispute_in_progress')->first();
                $child_statuses = $this->status->where('parent', $status_disputed->id)->pluck('id')->toArray();

                return array_merge([$status_disputed->id], $child_statuses);
            case 'resolved':
                $status_resolved = $this->status->where('slug', 'resolved')->first();
                $child_statuses = $this->status->where('parent', $status_resolved->id)->pluck('id')->toArray();

                return array_merge([$status_resolved->id], $child_statuses);
            default:
                return $this->status->pluck('id')->toArray();
        }
    }

    #[On('successModal')]
    public function successModal($message)
    {
        $this->closeModal();
        if (isset($message['header'])) {
            session()->flash('success', $message['header']);
        }

        if (isset($message['message'])) {
            session()->flash('success_message', $message['message']);
        }
    }

    #[On('failedModal')]
    public function failedModal($message)
    {
        $this->closeModal();
        if (isset($message['header'])) {
            session()->flash('error', $message['header']);
        }

        if (isset($message['message'])) {
            session()->flash('error_message', $message['message']);
        }
    }

    private function get_date_from()
    {
        $now = Carbon::now();
        switch ($this->date) {
            case 'today':
                return $now->copy()->startOfDay();
            case 'past_week':
                return $now->copy()->subDays(7)->startOfDay();
            case 'past_month':
                return $now->copy()->subMonth()->startOfDay();
            case 'past_6_months':
                return $now->copy()->subMonths(6)->startOfDay();
            case 'past_year':
                return $now->copy()->subYear()->startOfDay();
            default:
                return null;
        }
    }

    private function get_amount_range()
    {
        switch ($this->amount) {
            case '0-4999':
                return [0, 4999];
            case '5000-9999':
                return [5000, 9999];
            case '10000-14999':
                return [10000, 14999];
            case '15000+':
                return [15000];
            default:
                return null;
        }
    }

    #[Layout('layouts.merchant.seller-center')]
    public function render()
    {
        $return_orders = $this->merchant->return_orders_through_products()->with([
            'reason',
            'status.parent_status',
            'product_order.buyer' => function (MorphTo $query) {
                $query->morphWith([
                    User::class => ['profile', 'media' => function ($q) {
                        $q->where('collection_name', 'profile_picture');
                    }],
                    Merchant::class => ['media' => function ($q) {
                        $q->where('collection_name', 'merchant_logo');
                    }]
                ]);
            },
            'product_order.product.first_image',
            'product_order.payment_option',
            'product_order.transaction'
        ]);

        if ($this->activeBox && in_array($this->activeBox, ['return_initiated', 'return_in_progress', 'rejected', 'dispute_in_progress', 'resolved'])) {
            $statuses = $this->get_statuses();
            $return_orders = $return_orders->whereIn('return_order_status_id', $statuses);
        }

        if ($this->date and in_array($this->date, $this->allowed_date_options)) {
            $date_from = $this->get_date_from();
            $return_orders = $return_orders->whereBetween('return_orders.created_at', [$date_from, Carbon::now()]);
        }

        if ($this->deadline and in_array($this->deadline, $this->allowed_deadline_options)) {
            $return_orders = $return_orders->whereRaw('DATE_ADD(return_orders.created_at, INTERVAL 96.5 HOUR) < DATE_ADD(UTC_TIMESTAMP(), INTERVAL ' . $this->deadline . ' HOUR)'); 
        }

        if ($this->amount || $this->delivery_type) {
            $return_orders = $return_orders->whereHas('product_order', function ($query) {
                if ($this->amount and in_array($this->amount, $this->allowed_amount_options)) {
                    $amount_range = $this->get_amount_range();
                    if (count($amount_range) == 2) {
                        $query->whereRaw("(product_orders.amount * product_orders.quantity) BETWEEN {$amount_range[0]} AND {$amount_range[1]}");
                    } elseif (count($amount_range) == 1) {
                        $query->whereRaw("(product_orders.amount * product_orders.quantity) > {$amount_range[0]}");
                    }
                }

                if ($this->delivery_type and in_array($this->delivery_type, $this->allowed_delivery_type_options)) {
                    $query->where('delivery_type', $this->delivery_type);
                }
            });
        }

        if ($this->search_value) {
            $return_orders = $return_orders->where(function ($query) {
                $query->whereHas('product_order', function ($q) {
                    $q->whereHas('product', function ($qq) {
                        $qq->where('name', 'like', '%' . $this->search_value . '%');
                    });
                    $q->orWhereHasMorph('buyer', [User::class], function ($qq) {
                        $qq->whereHas('profile', function ($qqq) {
                            $qqq->where('first_name', 'like', '%' . $this->search_value . '%');
                            $qqq->orWhere('surname', 'like', '%' . $this->search_value . '%');
                        });
                    });
                    $q->orWhereHasMorph('buyer', [Merchant::class], function ($qq) {
                        $qq->where('name', 'like', '%' . $this->search_value . '%');
                    });
                });
                $query->orWhere('return_orders.id', 'like', '%' . $this->search_value . '%');
            });
        }

        $return_orders = $return_orders->orderBy('created_at', 'desc')->paginate(8);
        $elements = $this->getPaginationElements($return_orders);

        return view('merchant.seller-center.logistics.return-orders.merchant-seller-center-logistics-return-orders-list')->with([
            'return_orders' => $return_orders,
            'elements' => $elements
        ]);
    }
}
