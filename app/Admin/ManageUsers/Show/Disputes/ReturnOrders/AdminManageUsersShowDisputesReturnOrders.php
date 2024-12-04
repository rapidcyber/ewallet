<?php

namespace App\Admin\ManageUsers\Show\Disputes\ReturnOrders;

use App\Models\User;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithImage;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class AdminManageUsersShowDisputesReturnOrders extends Component
{
    use WithPagination, WithCustomPaginationLinks, WithImage;
    public User $user;
    public $searchTerm = '';

    public function mount(User $user)
    {
        $this->user = $user->load('profile');
    }

    #[Computed]
    public function return_orders_disputes_count()
    {
        return $this->user->return_orders()->count();
    }

    #[Computed]
    public function transaction_disputes_count()
    {
        return $this->user->transaction_disputes()->count();
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $return_orders = $this->user->return_orders()->with([
            'reason',
            'status.parent_status',
            'product_order.buyer',
            'product_order.product.first_image',
            'product_order.product.merchant',
        ]);

        if ($this->searchTerm) {
            $return_orders = $return_orders->where(function ($query) {
                $query->whereHas('product_order', function ($product_order) {
                    $product_order->whereHas('product', function ($product) {
                        $product->where('name', 'like', '%' . $this->searchTerm . '%');
                    });
                });
                $query->orWhere('return_orders.id', 'like', '%' . $this->searchTerm . '%');
                // $query->orWhereHas('product_order', function ($product_order) {
                //     $product_order->where('order_number', 'like', '%' . $this->searchTerm . '%');
                //     $product_order->orWhere('tracking_number', 'like', '%' . $this->searchTerm . '%');
                // });
            });
        }

        $return_orders = $return_orders->orderBy('created_at', 'desc')->paginate(10);
        $elements = $this->getPaginationElements($return_orders);

        return view('admin.manage-users.show.disputes.return-orders.admin-manage-users-show-disputes-return-orders')->with([
            'return_orders' => $return_orders,
            'elements' => $elements,
        ]);
    }
}
