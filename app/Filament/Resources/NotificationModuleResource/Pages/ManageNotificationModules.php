<?php

namespace App\Filament\Resources\NotificationModuleResource\Pages;

use App\Filament\Resources\NotificationModuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageNotificationModules extends ManageRecords
{
    protected static string $resource = NotificationModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
