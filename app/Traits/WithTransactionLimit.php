<?php

namespace App\Traits;
use App\Models\Merchant;
use App\Models\TransactionLimit;
use App\Models\TransactionType;
use App\Models\User;

trait WithTransactionLimit
{
    /**
     * Check if the user is limited to create a transaction.
     * Will return `true` if the user is limited, `false` otherwise.
     * 
     * @param \App\Models\Merchant|\App\Models\User $entity
     * @param \App\Models\TransactionType $transaction_type
     * @param float $amount
     * @return bool
     */
    public function check_outbound_limit(Merchant|User $entity, TransactionType $transaction_type, float $amount): bool
    {
        if (get_class($entity) == Merchant::class) {
            $pivot_column = 'merchant_id';
        } else {
            $pivot_column = 'user_id';
        }

        /// Get ids of user roles
        $roles = $entity->roles()->withPivot($pivot_column, 'role_id')->pluck('roles.id')->toArray();

        /// Get transaction limit with the largest amount
        $limit = $transaction_type->transaction_limits()->whereIn('role_id', $roles)
            ->orderByDesc('amount')
            ->get(['amount', 'scope'])
            ->first();

        /// If limit is null, it means there's no limit for the current transaction type.
        if (empty($limit)) {
            return false;
        }

        /// Get sum of transaction amount based on limit scope
        if ($limit->scope == 'daily') {
            $end = now()->format('Y-m-d H:i:s');
            $start = now()->startOfDay()->format('Y-m-d H:i:s');
        } else {
            $end = now()->format('Y-m-d H:i:s');
            $start = now()->startOfMonth()->format('Y-m-d H:i:s');
        }

        $transaction_sum = $entity->outgoing_transactions()
            ->where('transaction_type_id', $transaction_type->id)
            ->whereHas('status', function ($q) {
                $q->where('slug', 'successful');
            })
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        $accumulated_amount = $transaction_sum + $amount;
        return $accumulated_amount > $limit->amount;
    }


    /**
     * Summary of check_inbound_limit
     * @param \App\Models\Merchant|\App\Models\User $entity
     * @param \App\Models\TransactionType $transaction_type
     * @param float $amount
     * @return bool
     */
    public function check_inbound_limit(Merchant|user $entity, TransactionType $transaction_type, float $amount): bool
    {
        if (get_class($entity) == Merchant::class) {
            $pivot_column = 'merchant_id';
        } else {
            $pivot_column = 'user_id';
        }

        /// Get ids of user roles
        $roles = $entity->roles()->withPivot($pivot_column, 'role_id')->pluck('roles.id')->toArray();

        /// Get transaction limit with the largest amount
        $limit = $transaction_type->transaction_limits()->whereIn('role_id', $roles)
            ->orderByDesc('amount')
            ->get(['amount', 'scope'])
            ->first();

        /// If limit is null, it means there's no limit for the current transaction type.
        if (empty($limit)) {
            return false;
        }

        /// Get sum of transaction amount based on limit scope
        if ($limit->scope == 'daily') {
            $end = now()->format('Y-m-d H:i:s');
            $start = now()->startOfDay()->format('Y-m-d H:i:s');
        } else {
            $end = now()->format('Y-m-d H:i:s');
            $start = now()->startOfMonth()->format('Y-m-d H:i:s');
        }

        $transaction_sum = $entity->incoming_transactions()
            ->where('transaction_type_id', $transaction_type->id)
            ->whereHas('status', function ($q) {
                $q->where('slug', 'successful');
            })
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        $accumulated_amount = $transaction_sum + $amount;
        return $accumulated_amount > $limit->amount;
    }
}
