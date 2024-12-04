<?php

namespace App\Http\Controllers;

use App\Models\CashInFacility;
use App\Models\SystemService;
use App\Traits\WithHttpResponses;
use Illuminate\Http\Request;

class SystemController extends Controller
{

    use WithHttpResponses;

    public function settings() {
        $services = SystemService::all();

        $cash_in_facilities = CashInFacility::all();

        return $this->success([
            'services' => $services,
            'cash_in_facilities' => $cash_in_facilities,
        ]);
    }
}
