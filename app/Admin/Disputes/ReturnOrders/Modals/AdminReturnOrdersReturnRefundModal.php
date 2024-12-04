<?php

namespace App\Admin\Disputes\ReturnOrders\Modals;

use App\Models\AdminLog;
use App\Models\Balance;
use App\Models\Merchant;
use App\Models\ReturnOrder;
use App\Models\ReturnOrderDisputeDecision;
use App\Models\ReturnOrderLog;
use App\Models\ReturnOrderStatus;
use App\Models\Transaction;
use App\Models\TransactionChannel;
use App\Models\TransactionProvider;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Models\User;
use App\Traits\WithImage;
use App\Traits\WithNumberGeneration;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AdminReturnOrdersReturnRefundModal extends Component
{
    use WithImage, WithNumberGeneration;

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
            ])
            ->firstOrFail();
    }

    public function return_refund()
    {
        $dispute = $this->return_order->dispute;

        $dispute_decision = new ReturnOrderDisputeDecision;
        $dispute_decision->return_order_dispute_id = $dispute->id;
        $dispute_decision->type = 'return_and_refund';

        $admin_log = new AdminLog;
        $admin_log->user_id = auth()->id();
        $admin_log->title = 'Return and Refunded return order ' . $this->return_order->id . ' for merchant ' . $this->merchant->id;

        $refund_amount = $this->return_order->product_order->amount * $this->return_order->product_order->quantity;
        $balance = $this->merchant->latest_balance()->first() ?? new Balance;
        if ($balance->amount < $refund_amount) {
            return session()->flash('error', 'Merchant has insufficient balance');
        }

        DB::beginTransaction();
        try {
            $dispute_decision->save();
            $admin_log->save();

            $buyer = $this->return_order->product_order->buyer;

            $provider = TransactionProvider::where('code', 'RPY')->firstOrFail();
            $channel = TransactionChannel::where('code', 'RPY')->firstOrFail();
            $type = TransactionType::where('code', 'RF')->firstOrFail();
            $status = TransactionStatus::where('slug', 'successful')->firstOrFail();

            $transaction = new Transaction;
            $transaction->sender_id = $this->merchant->id;
            $transaction->sender_type = Merchant::class;
            $transaction->recipient_id = $buyer->id;
            $transaction->recipient_type = get_class($buyer);
            $transaction->txn_no = $this->generate_transaction_number();
            $transaction->ref_no = $this->generate_transaction_reference_number($provider, $channel, $type);
            $transaction->currency = 'PHP';
            $transaction->amount = $refund_amount;
            $transaction->service_fee = 0;
            $transaction->transaction_provider_id = $provider->id;
            $transaction->transaction_channel_id = $channel->id;
            $transaction->transaction_type_id = $type->id;
            $transaction->rate = 1;
            $transaction->transaction_status_id = $status->id;
            $transaction->save();

            $this->credit($this->merchant, $transaction);
            
            $this->debit($buyer, $transaction);
            
            $status_refunded_only = ReturnOrderStatus::where('slug', 'returned_and_refunded')->firstOrFail();
            $this->return_order->return_order_status_id = $status_refunded_only->id;
            $this->return_order->save();

            $log = new ReturnOrderLog;
            $log->return_order_id = $this->return_order->id;
            $log->return_order_status_id = $status_refunded_only->id;
            $log->title = 'Admin resolved dispute - Return and Refund';
            $log->description = "Admin has resolved the dispute. Product is to be returned to buyer. Merchant has successfully processed a refund of " . $transaction->currency . number_format($refund_amount, 2) . " to buyer. Transaction reference number: " . $transaction->ref_no;
            $log->save();

            DB::commit();
            $this->button_clickable = false;
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AdminReturnOrdersReturnRefundModal.return_refund - ' . $th->getMessage());
            session()->flash('error', 'Failed to process return and refund');
            session()->flash('error_message', 'Please try again later');
            $this->button_clickable = true;
            return;
        }

        $this->dispatch('successModal', [
            'header' => 'Success',
            'message' => 'Return and refund has been successfully processed'
        ]);
    }

    public function render()
    {
        return view('admin.disputes.return-orders.modals.admin-return-orders-return-refund-modal');
    }
}
