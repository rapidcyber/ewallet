<?php

namespace App\Filament\Resources\CashInFacilityResource\Pages;

use App\Filament\Resources\CashInFacilityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCashInFacilities extends ListRecords
{
    protected static string $resource = CashInFacilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
