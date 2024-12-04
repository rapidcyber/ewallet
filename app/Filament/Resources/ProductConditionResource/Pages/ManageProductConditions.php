<?php

namespace App\Filament\Resources\ProductConditionResource\Pages;

use App\Filament\Resources\ProductConditionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageProductConditions extends ManageRecords
{
    protected static string $resource = ProductConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
