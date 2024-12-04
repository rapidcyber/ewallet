<?php

namespace App\Merchant\SellerCenter\Logistics\ReturnOrders\Modals;

use App\Models\Balance;
use App\Models\Merchant;
use App\Models\ReturnOrder;
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

class ProcessRefundModal extends Component
{
    use WithNumberGeneration, WithImage;

    public Merchant $merchant;
    public ReturnOrder $return_order;

    #[Locked]
    public $button_clickable = true;

    public function mount(Merchant $merchant, $return_order_id)
    {
        $this->merchant = $merchant;
        $status_allowed = ReturnOrderStatus::where('slug', 'pending_return')->firstOrFail();

        $this->return_order = $merchant->return_orders_through_products()
            ->where('return_orders.id', $return_order_id)
            ->where('return_order_status_id', $status_allowed->id)
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

    public function process_refund()
    {
        if (!$this->return_order->status->slug == 'pending_return') {
            return session()->flash('error', 'Invalid status');
        }

        $refund_amount = $this->return_order->product_order->amount * $this->return_order->product_order->quantity;
        $balance = $this->merchant->latest_balance()->first() ?? new Balance;
        if ($balance->amount < $refund_amount) {
            return session()->flash('error', 'Insufficient balance');
        }

        DB::beginTransaction();
        try {
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

            $merchant_balance = new Balance;
            $merchant_balance->entity_id = $this->merchant->id;
            $merchant_balance->entity_type = Merchant::class;
            $merchant_balance->transaction_id = $transaction->id;
            $merchant_balance->amount = $balance->amount - $refund_amount;
            $merchant_balance->currency = 'PHP';
            $merchant_balance->save();
            
            $buyer_current_balance = $buyer->latest_balance()->first() ?? new Balance;

            $buyer_balance = new Balance;
            $buyer_balance->entity_id = $buyer->id;
            $buyer_balance->entity_type = get_class($buyer);
            $buyer_balance->transaction_id = $transaction->id;
            $buyer_balance->amount = $buyer_current_balance->amount + $refund_amount;
            $buyer_balance->currency = 'PHP';
            $buyer_balance->save();

            $status_refunded_only = ReturnOrderStatus::where('slug', 'returned_and_refunded')->firstOrFail();
            $this->return_order->return_order_status_id = $status_refunded_only->id;
            $this->return_order->save();

            $log = new ReturnOrderLog;
            $log->return_order_id = $this->return_order->id;
            $log->return_order_status_id = $status_refunded_only->id;
            $log->title = "Refund Processed";
            $log->description = "Seller has approved refund for the buyer. Refund amount: " . $refund_amount . ' Transaction Ref No: ' . $transaction->ref_no;
            $log->save();

            $log_resolved = new ReturnOrderLog;
            $log_resolved->return_order_id = $this->return_order->id;
            $log_resolved->return_order_status_id = $status_refunded_only->id;
            $log_resolved->title = "Request has been resolved";
            $log_resolved->description = "The return request has been resolved with the product being returned and refunded.";
            $log_resolved->save();

            $this->button_clickable = false;
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('MerchantSellerCenterLogisticsReturnOrders - ProcessRefundModal.refund - ' . $th->getMessage());
            session()->flash('error', 'Failed to process refund');
            session()->flash('error_message', 'Please try again later');
            $this->button_clickable = true;
        }
        $this->dispatch('successModal', [
            'header' => 'Success',
            'message' => 'Refund to buyer successful'
        ]);
    }

    public function render()
    {
        return view('merchant.seller-center.logistics.return-orders.modals.process-refund-modal');
    }
}
