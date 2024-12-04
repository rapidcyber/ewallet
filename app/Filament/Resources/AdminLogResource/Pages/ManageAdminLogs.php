<?php

namespace App\Filament\Resources\AdminLogResource\Pages;

use App\Filament\Resources\AdminLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAdminLogs extends ManageRecords
{
    protected static string $resource = AdminLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }
}
