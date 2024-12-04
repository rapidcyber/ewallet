<?php

namespace App\Admin\ManageUsers\Show\Disputes\ReturnOrders;

use App\Models\Merchant;
use App\Models\ReturnOrder;
use App\Models\User;
use App\Traits\WithImage;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class AdminManageUsersShowDisputesReturnOrdersDetails extends Component
{
    use WithImage;
    
    public ReturnOrder $return_order;
    public Merchant $merchant;
    public $visible = false;
    public $action = '';

    #[Locked]
    public $button_clickable = true;

    public function mount(User $user, ReturnOrder $returnOrder)
    {
        $user = $user->load('profile');
        $this->return_order = $user->return_orders()->with([
            'dispute.media',
            'dispute.response.media',
            'dispute.decision.media',
            'cancellation.reason',
            'cancellation.media',
            'reason',
            'status.parent_status',
            'product_order.product.first_image',
            'product_order.product.merchant',
            'product_order.buyer' => function (MorphTo $query) {
                $query->morphWith([
                    User::class => ['profile'],
                ]);
            },
        ])->where('return_orders.id', $returnOrder->id)->firstOrFail();

        $this->merchant = $this->return_order->product_order->product->merchant;
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

    #[On('closeModal')]
    public function closeModal()
    {
        $this->reset('action');
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        return view('admin.disputes.return-orders.admin-disputes-return-orders-show');
    }
}
