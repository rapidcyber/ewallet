<?php

namespace App\Filament\Resources\SystemServiceResource\Pages;

use App\Filament\Resources\SystemServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSystemService extends ViewRecord
{
    protected static string $resource = SystemServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
