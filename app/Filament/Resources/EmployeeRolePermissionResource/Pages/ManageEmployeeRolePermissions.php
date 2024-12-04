<?php

namespace App\Filament\Resources\EmployeeRolePermissionResource\Pages;

use App\Filament\Resources\EmployeeRolePermissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageEmployeeRolePermissions extends ManageRecords
{
    protected static string $resource = EmployeeRolePermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
