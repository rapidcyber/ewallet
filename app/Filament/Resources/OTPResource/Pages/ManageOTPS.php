<?php

namespace App\Filament\Resources\OTPResource\Pages;

use App\Filament\Resources\OTPResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageOTPS extends ManageRecords
{
    protected static string $resource = OTPResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
