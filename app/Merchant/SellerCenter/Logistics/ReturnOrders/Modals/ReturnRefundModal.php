<?php

namespace App\Merchant\SellerCenter\Logistics\ReturnOrders\Modals;

use App\Models\Merchant;
use App\Models\ReturnOrder;
use App\Models\ReturnOrderLog;
use App\Models\ReturnOrderStatus;
use App\Models\User;
use App\Traits\WithImage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ReturnRefundModal extends Component
{
    use WithImage;

    public Merchant $merchant;
    public ReturnOrder $return_order;

    #[Locked]
    public $button_clickable = true;

    public function mount(Merchant $merchant, $return_order_id)
    {
        $this->merchant = $merchant;
        $status_allowed = ReturnOrderStatus::where(function ($query) {
            $query->where('slug', 'return_initiated');
            $query->orWhereHas('parent_status', function ($q) {
                $q->where('slug', 'rejected');
            });
        })->pluck('id')->toArray();

        $this->return_order = $merchant->return_orders_through_products()
            ->where('return_orders.id', $return_order_id)
            ->whereIn('return_order_status_id', $status_allowed)
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
                'product_order.payment_option',
                'status.parent_status',
                'reason',
            ])
            ->firstOrFail();
    }

    public function calculate_remaining_hours($created_at)
    {
        $date = Carbon::parse($created_at);
        $target_time = $date->copy()->addHours(96.5);

        if ($target_time->lt(Carbon::now())) {
            return 'Expired';
        }

        return $target_time->diffForHumans(null, true) . ' left';
    }

    public function process_return()
    {
        if (!$this->return_order->status->slug == 'return_initiated' && !$this->return_order->status->parent_status->slug == 'rejected') {
            return session()->flash('error', 'Invalid status');
        }

        DB::beginTransaction();
        try {
            $status_refunded_only = ReturnOrderStatus::where('slug', 'pending_return')->firstOrFail();
            $this->return_order->return_order_status_id = $status_refunded_only->id;
            $this->return_order->save();

            $log = new ReturnOrderLog;
            $log->return_order_id = $this->return_order->id;
            $log->return_order_status_id = $status_refunded_only->id;
            $log->title = 'Seller accepted return request';
            $log->description = 'Seller has accepted the return request and the return process has started';
            $log->save();

            $this->button_clickable = false;
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('MerchantSellerCenterLogisticsReturnOrders - ReturnRefundModal.process_return - ' . $th->getMessage());
            session()->flash('error', 'Failed to process return and refund');
            session()->flash('error_message', 'Please try again later');
            $this->button_clickable = true;
        }
        $this->dispatch('successModal', [
            'header' => 'Success',
            'message' => 'Please process the return before proceeding with the refund',
        ]);
    }

    public function render()
    {
        return view('merchant.seller-center.logistics.return-orders.modals.return-refund-modal');
    }
}
