<?php

namespace App\Filament\Resources\SystemServiceResource\Pages;

use App\Filament\Resources\SystemServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSystemServices extends ListRecords
{
    protected static string $resource = SystemServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
