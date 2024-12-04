<?php

namespace App\Filament\Resources\ShippingStatusResource\Pages;

use App\Filament\Resources\ShippingStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageShippingStatuses extends ManageRecords
{
    protected static string $resource = ShippingStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
