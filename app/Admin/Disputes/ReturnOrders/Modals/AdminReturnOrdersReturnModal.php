<?php

namespace App\Admin\Disputes\ReturnOrders\Modals;

use App\Models\AdminLog;
use App\Models\Merchant;
use App\Models\ReturnOrder;
use App\Models\ReturnOrderDisputeDecision;
use App\Models\ReturnOrderLog;
use App\Models\ReturnOrderStatus;
use App\Models\User;
use App\Traits\WithImage;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AdminReturnOrdersReturnModal extends Component
{
    use WithImage;

    public Merchant $merchant;
    public ReturnOrder $return_order;
    public $visible = true;

    #[Locked]
    public $button_clickable = true;

    public function mount(Merchant $merchant, $return_order_id)
    {
        $this->merchant = $merchant;

        $allowed_status = ReturnOrderStatus::where('slug', 'pending_resolution')
            ->whereHas('parent_status', function ($q) {
                $q->where('slug', 'dispute_in_progress');
            })
            ->firstOrFail();

        $this->return_order = $merchant->return_orders_through_products()
            ->where('return_orders.id', $return_order_id)
            ->where('return_orders.return_order_status_id', $allowed_status->id)
            ->whereHas('dispute', function ($q) {
                $q->whereDoesntHave('decision');
            })
            ->with([
                'product_order.buyer' => function (MorphTo $q) {
                    $q->morphWith([
                        User::class => ['profile', 'media' => function ($q) {
                            $q->where('collection_name', 'profile_picture');
                        }],
                        Merchant::class => ['media' => function ($q) {
                            $q->where('collection_name', 'merchant_logo');
                        }]
                    ]);
                },
                'product_order.product.first_image',
                'reason',
                'dispute',
            ])
            ->firstOrFail();
    }

    public function return()
    {
        $dispute = $this->return_order->dispute;

        $dispute_decision = new ReturnOrderDisputeDecision;
        $dispute_decision->return_order_dispute_id = $dispute->id;
        $dispute_decision->type = 'return';

        $admin_log = new AdminLog;
        $admin_log->user_id = auth()->id();
        $admin_log->title = 'Returned return order ' . $this->return_order->id . ' for merchant ' . $this->merchant->id;

        $status_refunded_only = ReturnOrderStatus::where('slug', 'returned_only')->firstOrFail();
        $this->return_order->return_order_status_id = $status_refunded_only->id;
        
        $return_log = new ReturnOrderLog;
        $return_log->return_order_id = $this->return_order->id;
        $return_log->return_order_status_id = $status_refunded_only->id;
        $return_log->title = 'Admin resolved dispute - Return only';
        $return_log->description = 'Admin has resolved the dispute. Buyer will be returning the product only.';
        
        DB::beginTransaction();
        try {
            $dispute_decision->save();
            $admin_log->save();
            $this->return_order->save();
            $return_log->save();

            DB::commit();
            $this->button_clickable = false;
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('AdminReturnOrdersReturnModal - ' . $ex->getMessage());
            session()->flash('error', 'Failed to process return and refund');
            session()->flash('error_message', 'Please try again later');
            $this->button_clickable = true;
            return;
        }

        $this->dispatch('successModal', [
            'header' => 'Success',
            'message' => 'Buyer to return the product',
        ]);
    }

    public function render()
    {
        return view('admin.disputes.return-orders.modals.admin-return-orders-return-modal');
    }
}
