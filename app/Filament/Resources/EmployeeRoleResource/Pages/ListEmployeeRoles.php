<?php

namespace App\Filament\Resources\EmployeeRoleResource\Pages;

use App\Filament\Resources\EmployeeRoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeRoles extends ListRecords
{
    protected static string $resource = EmployeeRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
