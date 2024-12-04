<?php

namespace App\Filament\Resources\MerchantCategoryResource\Pages;

use App\Filament\Resources\MerchantCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMerchantCategories extends ManageRecords
{
    protected static string $resource = MerchantCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
