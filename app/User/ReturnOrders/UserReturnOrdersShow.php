<?php

namespace App\User\ReturnOrders;

use App\Models\ReturnOrder;
use App\Models\User;
use App\Traits\WithImage;
use App\Traits\WithValidPhoneNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class UserReturnOrdersShow extends Component
{
    use WithImage, WithValidPhoneNumber;

    public ReturnOrder $returnOrder;
    public $confirmationModalVisible = false;
    
    #[Locked]
    public $actionType = 'request';

    #[Locked]
    public $show_modal = false;

    public function mount(ReturnOrder $returnOrder)
    {
        $user = auth()->user();
        $this->returnOrder = ReturnOrder::where('id', $returnOrder->id)
            ->whereHas('product_order', function ($query) use ($user) {
                $query->whereHasMorph('buyer', [User::class], function ($q) use ($user) {
                    $q->where('id', $user->id);
                });
            })
            ->with([
                'product_order.product.first_image',
                'product_order.product.merchant.logo',
                'product_order.buyer',
                'product_order.location',
                'product_order.payment_option',
                'product_order.shipping_option',
                'product_order.logs',
                'dispute',
                'status.parent_status',
                'reason',
                'media',
                'rejection.reason',
                'rejection.media',
            ])
            ->firstOrFail();
    }

    public function showModal()
    {
        if ($this->returnOrder->status->isRejected()) {
            return $this->show_modal = true;
        }

        session()->flash('error', 'Dispute cannot be created');
        session()->flash('error_message', 'Only rejected return orders can be disputed');
    }

    #[On('closeModal')]
    public function closeModal()
    {
        $this->show_modal = false;
    }

    public function cancel_request()
    {
        if (!$this->returnOrder->status->isCancellable()) {
            session()->flash('error', 'Request cannot be cancelled');
            session()->flash('error_message', 'Only pending and rejected return orders can be cancelled');
            return;
        }
        
        DB::beginTransaction();
        try {
            $this->returnOrder->delete();
            session()->flash('success', 'Request cancelled successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('UserReturnOrdersShow.cancel_request: ' . $th->getMessage());
            session()->flash('error', 'An error has occurred. Please try again later.');
            return;
        }

        return $this->redirect(route('user.return-orders.index'));
    }

    #[Layout('layouts.user')]
    public function render()
    {
        return view('user.return-orders.user-return-orders-show');
    }
}
