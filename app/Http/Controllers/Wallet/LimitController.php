<?php

namespace App\Http\Controllers\Wallet;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\TransactionLimit;
use App\Models\TransactionType;
use App\Traits\WithEntity;
use App\Traits\WithHttpResponses;
use Illuminate\Http\Request;

class LimitController extends Controller
{
    use WithHttpResponses, WithEntity;

    public function __invoke()
    {
        $limits = TransactionLimit::whereHas('transaction_type', function ($q) {
                $q->whereIn('code', ['CO', 'CI', 'TR']);
            })
            ->with(['transaction_type:id,name,code', 'role:id,slug'])
            ->get();

        $details = [];

        foreach ($limits as $limit) {
            if (empty($details[$limit->transaction_type->name])) {
                $details[$limit->transaction_type->name] = [];
            }

            if (empty($details[$limit->transaction_type->name][$limit->role->slug])) {
                $details[$limit->transaction_type->name][$limit->role->slug] = [];
            }

            $details[$limit->transaction_type->name][$limit->role->slug]['amount'] = $limit->amount;
            $details[$limit->transaction_type->name][$limit->role->slug]['scope'] = $limit->scope;
        }

        return $this->success($details);
    }
}
