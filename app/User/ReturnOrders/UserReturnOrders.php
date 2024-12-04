<?php

namespace App\User\ReturnOrders;

use App\Models\ReturnOrder;
use App\Models\ReturnOrderLog;
use App\Models\ReturnOrderStatus;
use App\Models\User;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class UserReturnOrders extends Component
{
    use WithPagination, WithCustomPaginationLinks, WithImage;

    public User $user;
    public $activeBox = '';
    public $searchTerm = '';
    public $confirmationModalVisible = false;
    #[Locked]
    public $cancel_request_id = null;
    public $show_cancel_request_modal = false;

    #[Locked]
    public $return_order_id = null;
    public $show_modal = false;

    protected $allowedActiveBoxOptions = [
        '',
        'pending',
        'returning',
        'rejected',
        'resolved',
        'disputed'
    ];

    public function mount()
    {
        $this->user = User::find(auth()->id());
    }

    #[Computed(persist: true)]
    public function status()
    {
        return ReturnOrderStatus::with(['parent_status', 'children'])->toBase()->get();
    }

    #[Computed(persist: true)]
    public function rejected_status()
    {
        return ReturnOrderStatus::where(function ($query) {
            $query->where('name', 'Rejected');
            $query->orWhere(function ($q) {
                $q->whereHas('parent_status', function ($q) {
                    $q->where('name', 'Rejected');
                });
            });
        })
            ->orWhere(function ($query) {
                $query->where('name', 'Dispute In Progress');
                $query->orWhere(function ($q) {
                    $q->whereHas('parent_status', function ($q) {
                        $q->where('name', 'Dispute In Progress');
                    });
                });
            })
            ->pluck('id')->toArray();
    }

    #[Computed(persist: true)]
    public function resolved_status()
    {
        return ReturnOrderStatus::where(function ($query) {
            $query->where('name', 'Resolved');
            $query->orWhereHas('parent_status', function ($q) {
                $q->where('name', 'Resolved');
            });
        })
            ->pluck('id')->toArray();
    }

    #[Computed(persist: true)]
    public function cancellable_status()
    {
        return ReturnOrderStatus::where(function ($query) {
            $query->whereIn('name', ['Return Initiated', 'Rejected', 'Dispute In Progress']);
            $query->orWhereHas('parent_status', function ($q) {
                $q->whereIn('name', ['Return Initiated', 'Rejected', 'Dispute In Progress']);
            });
        })->pluck('id')->toArray();
    }

    #[Computed(persist: true)]
    public function dispute_status()
    {
        return ReturnOrderStatus::where(function ($query) {
            $query->where('name', 'Dispute In Progress');
            $query->orWhereHas('parent_status', function ($q) {
                $q->where('name', 'Dispute In Progress');
            });
        })
            ->pluck('id')->toArray();
    }

    #[Computed]
    public function count_all()
    {
        return $this->user->return_orders()->count();
    }

    #[Computed]
    public function count_pending()
    {
        return $this->user->return_orders()->where('return_order_status_id', $this->status->where('name', 'Return Initiated')->first()->id)->count();
    }

    #[Computed]
    public function count_return()
    {
        return $this->user->return_orders()->whereIn('return_order_status_id', $this->status->whereIn('name', ['Pending Return', 'Return In Progress'])->pluck('id')->toArray())->count();
    }

    #[Computed]
    public function count_rejected()
    {
        return $this->user->return_orders()->whereIn('return_order_status_id', $this->rejected_status)->count();
    }

    #[Computed]
    public function count_resolved()
    {
        return $this->user->return_orders()->whereIn('return_order_status_id', $this->resolved_status)->count();
    }

    #[Computed]
    public function count_disputed()
    {
        return $this->user->return_orders()->whereIn('return_order_status_id', $this->dispute_status)->count();
    }

    public function updatedActiveBox()
    {
        if (! in_array($this->activeBox, $this->allowedActiveBoxOptions)) {
            $this->activeBox = '';
        }

        $this->resetPage();
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    #[On('closeModal')]
    public function closeModal()
    {
        $this->return_order_id = null;
        $this->show_modal = false;
        $this->cancel_request_id = null;
        $this->show_cancel_request_modal = false;
    }

    public function open_dispute_modal($return_order_id)
    {
        $return_order = $this->user->return_orders()->where('return_orders.id', $return_order_id)->whereIn('return_order_status_id', $this->rejected_status)->first();
        if (! $return_order) {
            session()->flash('error', 'Error: Return Order not found');
            session()->flash('error_message', 'The return order does not exist.');
            return;
        }

        $this->return_order_id = $return_order_id;
        $this->show_modal = true;
    }

    public function open_cancel_request_modal($return_order_id)
    {
        $return_order = $this->user->return_orders()->where('return_orders.id', $return_order_id)->whereIn('return_order_status_id', $this->cancellable_status)->first();
        if (! $return_order) {
            session()->flash('error', 'Error: Return Order not found');
            session()->flash('error_message', 'The return order does not exist.');
            return;
        }

        $this->cancel_request_id = $return_order_id;
        $this->show_cancel_request_modal = true;
    }

    #[On('successSubmit')]
    public function success_submit($message)
    {
        session()->flash('success', $message['header']);
        if (isset($message['message'])) {
            session()->flash('success_message', $message['message']);
        }
    }

    #[On('failedSubmit')]
    public function failed_submit($message)
    {
        session()->flash('error', $message['header']);
        if (isset($message['message'])) {
            session()->flash('error_message', $message);
        }
    }

    public function updatedShowModal()
    {
        if ($this->show_modal == false) {
            $this->return_order_id = null;
        }
    }

    public function cancel_request()
    {
        if (!$this->cancel_request_id) {
            session()->flash('error', 'Return order not found');
            return;
        }

        $return_order = $this->user->return_orders()
            ->where('return_orders.id', $this->cancel_request_id)
            ->whereIn('return_order_status_id', $this->cancellable_status)
            ->first();

        if (!$return_order) {
            session()->flash('error', 'Error: Return Order not found');
            session()->flash('error_message', 'The return order does not exist.');
            $this->closeModal();
            return;
        }

        DB::beginTransaction();
        try {
            $cancelled_status = ReturnOrderStatus::where('slug', str('Return Cancelled')->slug('_'))->firstOrFail();

            $return_order->return_order_status_id = $cancelled_status->id;
            $return_order->save();

            $log = new ReturnOrderLog;
            $log->return_order_id = $return_order->id;
            $log->return_order_status_id = $cancelled_status->id;
            $log->title = 'Request Cancelled';
            $log->description = 'The return order request has been cancelled by the buyer';
            $log->save();

            DB::commit();
            session()->flash('success', 'Success!');
            session()->flash('success_message', 'The return order request has been cancelled successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('UserReturnOrders.cancel_request - ' .  $th->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later');
        }

        $this->closeModal();
    }

    public function open_return_orders_show($return_order_id)
    {
        $return_order = $this->user->return_orders()
            ->where('return_orders.id', $return_order_id)
            ->first();

        if (!$return_order) {
            session()->flash('error', 'Error: Return Order not found');
            session()->flash('error_message', 'The return order does not exist.');
            return;
        }

        return $this->redirect(route('user.return-orders.show', ['returnOrder' => $return_order]));
    }

    #[Layout('layouts.user')]
    public function render()
    {
        $return_orders = $this->user->return_orders();

        if ($this->activeBox) {
            $return_orders = match ($this->activeBox) {
                'pending' => $return_orders->where('return_order_status_id', $this->status->where('name', 'Return Initiated')->first()->id),
                'returning' => $return_orders->whereIn('return_order_status_id', $this->status->whereIn('name', ['Pending Return', 'Return In Progress'])->pluck('id')->toArray()),
                'rejected' => $return_orders->whereIn('return_order_status_id', $this->rejected_status),
                'resolved' => $return_orders->whereIn('return_order_status_id', $this->resolved_status),
                'disputed' => $return_orders->whereIn('return_order_status_id', $this->dispute_status),
                default => $return_orders
            };
        }

        if ($this->searchTerm) {
            $return_orders = $return_orders->where(function ($query) {
                $query->whereHas('product_order', function ($product_order) {
                    $product_order->whereHas('product', function ($product) {
                        $product->where('name', 'like', '%' . $this->searchTerm . '%');
                    });
                });
                $query->orWhere('return_orders.id', 'like', '%' . $this->searchTerm . '%');
                $query->orWhereHas('product_order', function ($product_order) {
                    $product_order->where('order_number', 'like', '%' . $this->searchTerm . '%');
                    $product_order->orWhere('tracking_number', 'like', '%' . $this->searchTerm . '%');
                });
            });
        }

        $return_orders = $return_orders->withCount('dispute')
            ->with(['reason', 'status.parent_status', 'product_order.product.first_image', 'product_order.product.merchant.logo'])
            ->orderBy('created_at', 'desc')
            ->paginate(7);

        $elements = $this->getPaginationElements($return_orders);

        return view('user.return-orders.user-return-orders')->with([
            'return_orders' => $return_orders,
            'elements' => $elements
        ]);
    }
}
