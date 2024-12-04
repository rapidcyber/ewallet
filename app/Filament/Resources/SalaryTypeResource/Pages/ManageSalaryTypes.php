<?php

namespace App\Filament\Resources\SalaryTypeResource\Pages;

use App\Filament\Resources\SalaryTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSalaryTypes extends ManageRecords
{
    protected static string $resource = SalaryTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
