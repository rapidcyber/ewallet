<?php

namespace App\Filament\Resources\QrGeneratedDataResource\Pages;

use App\Filament\Resources\QrGeneratedDataResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageQrGeneratedData extends ManageRecords
{
    protected static string $resource = QrGeneratedDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
