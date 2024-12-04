<?php

namespace App\Filament\Resources\TransactionStatusResource\Pages;

use App\Filament\Resources\TransactionStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransactionStatuses extends ListRecords
{
    protected static string $resource = TransactionStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
