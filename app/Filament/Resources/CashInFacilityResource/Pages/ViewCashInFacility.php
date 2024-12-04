<?php

namespace App\Filament\Resources\CashInFacilityResource\Pages;

use App\Filament\Resources\CashInFacilityResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCashInFacility extends ViewRecord
{
    protected static string $resource = CashInFacilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
