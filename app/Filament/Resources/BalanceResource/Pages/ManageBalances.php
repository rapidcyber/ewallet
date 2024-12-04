<?php

namespace App\Filament\Resources\BalanceResource\Pages;

use App\Filament\Resources\BalanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageBalances extends ManageRecords
{
    protected static string $resource = BalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
