<?php

namespace App\Traits;

use App\Models\Invoice;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Authenticatable;

trait WithEntity
{
    public function get(
        Authenticatable|User $user,
        ?string $merchant_account_number = null,
    ): User|Merchant|null {
        $entity = $user;
        /// If `merchant_account_number` is not null, get the merchant using the `merchant_account_number` 
        /// associated with the currently authenticated user.
        if (!empty($merchant_account_number)) {
            $entity = $entity->merchants()
                ->where(['account_number' => $merchant_account_number, 'status' => 'verified'])
                ->first();
        }
        return $entity;
    }

    public function is_same(Merchant|User $entity_a, Merchant|User $entity_b): bool
    {
        $a_class = get_class($entity_a);
        $b_class = get_class($entity_b);
        if ($a_class !== $b_class)
            return false;

        return $entity_a->id === $entity_b->id;
    }

    /**
     * Determine if a transaction is inbound (the provided entity is the recipient of the transaction).
     * 
     * @param \App\Models\Transaction $transaction
     * @return bool
     */
    public function is_inbound_transaction(Transaction $transaction, Merchant|User $entity): bool
    {
        return $transaction->recipient_type == get_class($entity) and
            $transaction->recipient_id == $entity->id;
    }

    public function is_inbound_invoice(Invoice $invoice, Merchant|User $entity): bool
    {
        return $invoice->recipient_type == get_class($entity) and
            $invoice->recipient_id == $entity->id;
    }

    /**
     * Summary of is_mine
     * 
     * @param \App\Models\Product|\App\Models\Service $item
     * @param \App\Models\Merchant|\App\Models\User $entity
     * @return bool
     */
    public function is_mine(Product|Service $item, Merchant|User $entity): bool
    {
        if (get_class($entity) != Merchant::class) {
            return false;
        }
        return $entity->id == $item->merchant_id;
    }
}
