<?php

namespace App\Filament\Resources\SystemIssueLogResource\Pages;

use App\Filament\Resources\SystemIssueLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSystemIssueLogs extends ManageRecords
{
    protected static string $resource = SystemIssueLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
