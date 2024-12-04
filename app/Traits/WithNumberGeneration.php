<?php

namespace App\Traits;

use App\Models\Bill;
use App\Models\Invoice;
use App\Models\Merchant;
use App\Models\OTP;
use App\Models\PaymentOption;
use App\Models\Product;
use App\Models\ShippingOption;
use App\Models\Transaction;
use App\Models\TransactionChannel;
use App\Models\TransactionProvider;
use App\Models\TransactionType;
use App\Models\User;
use Illuminate\Auth\Authenticatable;

trait WithNumberGeneration
{
    public function generate_ticket_number($module): string|false
    {
        $module = strtoupper($module);
        if (in_array($module, ['INQUIRY', 'FEEDBACK'])) {
            $now = time();

            return substr($module, 0, 2) . '-' . $now;
        } else {
            return false;
        }
    }

    /**
     * Generates a unique product SKU for the given entity.
     *
     * The generated SKU is a combination of 4 random alphabets and 8 random numbers.
     * It checks for the existence of the generated SKU in the database and regenerates
     * if it already exists.
     *
     * @param Merchant $merchant The merchant for which the SKU is being generated
     * @return string The generated product SKU
     */
    public function generate_product_sku(Merchant $merchant): string
    {
        $alpha = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $alphaLen = strlen($alpha);
        $randomAlpha = '';
        for ($i = 0; $i < 4; $i++) {
            $randomAlpha = $randomAlpha . $alpha[rand(0, $alphaLen - 1)];
        }

        $numeric = '0123456789';
        $numericLen = strlen($numeric);
        $randomNumeric = '';
        for ($i = 0; $i < 8; $i++) {
            $randomNumeric = $randomNumeric . $numeric[rand(0, $numericLen - 1)];
        }

        $sku = $randomAlpha . $randomNumeric;
        $check_sku = Product::where('sku', $sku)
            ->exists();

        if ($check_sku) {
            return $this->generate_product_sku($merchant);
        }

        return $sku;
    }

    /**
     * Generate otp and save phone_number as reference
     *
     * @param string $phone_number
     * @return \App\Models\OTP
     */
    public function generate_otp_code(string $contact, string $type): OTP
    {
        $otp = OTP::firstOrNew(['contact' => $contact]);
        $otp->fill([
            'contact' => $contact,
            'code' => str_pad(strval(random_int(000000, 999999)), 6, '0', STR_PAD_LEFT),
            'verification_id' => bin2hex(date_format(now(), 'md') . mt_rand(100000, 999999)),
            'expires_at' => now()->addMinutes(4),
            'type' => $type,
        ]);
        $otp->save();

        return $otp;
    }

    /**
     * Generate transaction number for transactions
     */
    public function generate_transaction_number(): string
    {
        return str_pad(Transaction::count() + 1, 12, '0', STR_PAD_LEFT);
    }

    /**
     * Generate a transaction reference number.
     *
     * Reference number format
     *  16 character Unique
     *  Format : TPTC-TT-YmdHis
     *
     *  TP - Transaction Provider
     *  TC - Transaction Channel
     *  TT - Transaction Type
     *  YmdHis - 2 digit year, 2 digit month, 2 digit date, 2 digit hour, 2 digit mins, 2 digit seconds
     *
     * NOTE: for invoices, we use the invoice's invoice_no for transaction reference number.
     *
     * @return string
     */
    public function generate_transaction_reference_number(
        TransactionProvider $provider,
        TransactionChannel $channel,
        TransactionType $type,
    ) {
        return $provider->code . $channel->code . $type->code . now()->format('ymdHis');
    }

    /**
     * Format: MMMMM–YYMMDD–AN-####
     *  - MMMMM: Unique per merchant
     *  - YYMMDD: Invoice date (220124)
     *  - AN: recipient account ID (user_id or merchant_id) (5 digit padded)
     *  - #: Incremented number (5 digit padded)
     *
     * @param  \App\Models\Merchant  $merchant
     */
    public function generate_invoice_number(Merchant|User $sender, Merchant|User $recipient): string
    {
        $prefix = $sender->invoice_prefix ?? 'RPY';
        $date = now()->format('ymd');
        $count = Invoice::where('invoice_no', 'like', "$prefix$date%")->count() + 1;
        $count = str_pad($count, 5, "0", STR_PAD_LEFT);
        $an = str_pad($recipient->id, 5, "0", STR_PAD_LEFT);

        $invoice_no = $prefix . $date . $an . $count;

        if (Invoice::where('invoice_no', $invoice_no)->exists()) {
            return $this->generate_invoice_number($sender, $recipient);
        }

        return $invoice_no;
    }

    /**
     * Format: [Y][UID][COUNT]
     * - Y : 2 digit year
     * - UID : User ID (8 digit padded left)
     * - COUNT : Merchant count from DB + 1 (6 digit padded left) 
     * @param \App\Models\User $user
     * @return string
     */
    public function generate_merchant_account_number(User|Authenticatable|null $user): string
    {
        $year = now()->format('y');
        $an = str_pad($user->id, 8, "0", STR_PAD_LEFT);
        $count = str_pad(Merchant::count() + 1, 6, "0", STR_PAD_LEFT);

        $account_number = $year . $an . $count;

        if (Merchant::where('account_number', $account_number)->exists()) {
            return $this->generate_merchant_account_number($user);
        }

        return $account_number;
    }

    /**
     * Generate invoicing prefix for merchant.
     * 
     * @return mixed
     */
    public function generate_invoice_prefix()
    {
        $count = str_pad(Merchant::count() + 1, 4, "0", STR_PAD_LEFT);

        $prefix = "M$count";
        if (Merchant::where('invoice_prefix', $prefix)->exists()) {
            return $this->generate_invoice_prefix();
        }

        return $prefix;
    }

    /**
     * Format
     *  - [MID]-[PID][ORDER_COUNT]-[SO][PO]
     * 
     * @param \App\Models\Product $product
     * @return void
     */
    public function generate_order_number(Product $product, ShippingOption $shipping_option, PaymentOption $payment_option): string
    {
        $merchant_id = str_pad($product->merchant->id, 4, "0", STR_PAD_LEFT);
        $product_id = str_pad($product->id, 6, "0", STR_PAD_LEFT);
        $order_count = str_pad($product->orders->count() + 1, 4, "0", STR_PAD_LEFT);

        $s_option = str_pad($shipping_option->id, 2, 0, STR_PAD_LEFT);
        $p_option = str_pad($payment_option->id, 2, 0, STR_PAD_LEFT);

        return $merchant_id . '-' . $product_id . $order_count . '-' . $s_option . $p_option;
    }


    /**
     * Summary of generate_bill_reference_number
     * @return string
     */
    public function generate_bill_reference_number(): string
    {
        $count = str_pad(Bill::count() + 1, 10, "0", STR_PAD_LEFT);
        $date = now()->format('ymd');

        return "$date$count";
    }
}
