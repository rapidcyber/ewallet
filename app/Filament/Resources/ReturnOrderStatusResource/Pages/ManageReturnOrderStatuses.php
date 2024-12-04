<?php

namespace App\Filament\Resources\ReturnOrderStatusResource\Pages;

use App\Filament\Resources\ReturnOrderStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageReturnOrderStatuses extends ManageRecords
{
    protected static string $resource = ReturnOrderStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
