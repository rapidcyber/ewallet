<?php

namespace App\Filament\Resources\TransactionLimitResource\Pages;

use App\Filament\Resources\TransactionLimitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransactionLimits extends ListRecords
{
    protected static string $resource = TransactionLimitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
