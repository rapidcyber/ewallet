<?php

namespace App\Filament\Resources\CashInFacilityResource\Pages;

use App\Filament\Resources\CashInFacilityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCashInFacility extends EditRecord
{
    protected static string $resource = CashInFacilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
