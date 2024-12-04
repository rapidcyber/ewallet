<?php

namespace App\Traits;
use App\Models\Balance;
use App\Models\BalanceLimit;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\User;

trait WithBalance
{
    use WithNumberGeneration;

    /**
     * Summary of check_balance_limit
     * @param \App\Models\Merchant|\App\Models\User $entity
     * @param float $amount
     * @return bool
     */
    private function is_balance_limit_reached(Merchant|User $entity, float $amount): bool
    {
        $balance = $entity->latest_balance()->firstOrNew();

        /// Get ids of roles
        $roles = $entity->roles()->pluck('roles.id')->toArray();

        /// Get balance limit with the largest amount
        $limit = BalanceLimit::whereIn('role_id', $roles)
            ->orderByDesc('amount')
            ->get(['amount'])
            ->first();

        $balance_amount = $balance->amount ?? 0;

        // dd($balance->amount, $amount, $limit->amount);
        /// if amount + current balance amount is greater than balance limit, return false;
        return ($balance_amount + $amount) > $limit->amount;
    }

    /**
     * Check if the current entity balance is sufficient for `amount`.
     * 
     * @param \App\Models\Merchant|\App\Models\User $entity
     * @param mixed $amount
     * @return bool
     */
    public function is_sufficient(Merchant|User $entity, $amount): bool
    {
        $balance = $entity->latest_balance()->firstOrNew();
        return 0 <= $balance->amount - $amount;
    }

    /**
     * debit `amount` to entity's latest balance amount.
     * 
     * @param \App\Models\Merchant|\App\Models\User $entity
     * @param mixed $amount
     * @return bool
     */
    public function debit(Merchant|User $entity, Transaction $transaction): bool
    {
        $balance = $entity->latest_balance()->firstOrNew();
        $new_balance = $balance->replicate();
        $new_balance->transaction_id = $transaction->id;
        $new_balance->amount += $transaction->amount;
        $new_balance->currency = $transaction->currency;
        $new_balance->created_at = now();
        return $new_balance->save();
    }

    /**
     * credit `amount` to entity's latest balance amount.
     * 
     * @param \App\Models\Merchant|\App\Models\User $entity
     * @param mixed $amount
     * @return bool
     */
    public function credit(Merchant|User $entity, Transaction $transaction, float $off_us_service_charge = null): bool
    {
        $balance = $entity->latest_balance()->firstOrNew();
        $new_balance = $balance->replicate();
        $new_balance->transaction_id = $transaction->id;
        $new_balance->amount -= ($transaction->amount + $transaction->service_fee + ($off_us_service_charge ??= 0));
        $new_balance->currency = $transaction->currency;
        $new_balance->created_at = now();
        return $new_balance->save();
    }

    /**
     * refund `amount` from entity's balance for a specific transaction.
     * 
     * @param \App\Models\Merchant|\App\Models\User $entity
     * @param \App\Models\Transaction $transaction
     * @return bool
     */
    public function refund(Merchant|User $entity, Transaction $transaction): bool
    {
        $balance = $entity->latest_balance()->firstOrNew();
        $new_balance = $balance->replicate();
        $new_balance->transaction_id = $transaction->id;
        $new_balance->amount += $transaction->amount + $transaction->service_fee;
        $new_balance->currency = $transaction->currency;
        $new_balance->created_at = now();
        return $new_balance->save();
    }
}
