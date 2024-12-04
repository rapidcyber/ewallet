<?php

namespace App\Filament\Resources\TransactionTypeResource\Pages;

use App\Filament\Resources\TransactionTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTransactionTypes extends ManageRecords
{
    protected static string $resource = TransactionTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
