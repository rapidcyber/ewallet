<?php

namespace App\Filament\Resources\SystemServiceResource\Pages;

use App\Filament\Resources\SystemServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSystemService extends EditRecord
{
    protected static string $resource = SystemServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
