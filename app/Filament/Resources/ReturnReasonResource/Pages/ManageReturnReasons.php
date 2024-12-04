<?php

namespace App\Filament\Resources\ReturnReasonResource\Pages;

use App\Filament\Resources\ReturnReasonResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageReturnReasons extends ManageRecords
{
    protected static string $resource = ReturnReasonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
