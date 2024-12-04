<?php

namespace App\Http\Controllers\Wallet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wallet\BalanceRequest;
use App\Traits\WithBalance;
use App\Traits\WithEntity;
use App\Traits\WithHttpResponses;

class BalanceController extends Controller
{
    use WithHttpResponses, WithEntity, WithBalance;

    /**
     * Handle the incoming request.
     */
    public function __invoke(BalanceRequest $request)
    {
        $validated = $request->validated();
        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        $balance = $entity->latest_balance()->firstOrNew([], [
            'id' => 0,
            'amount' => '0',
            'currency' => 'PHP',
        ]);
        return $this->success($balance);
    }
}
