<?php

namespace App\Filament\Resources\TransactionProviderResource\Pages;

use App\Filament\Resources\TransactionProviderResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTransactionProviders extends ManageRecords
{
    protected static string $resource = TransactionProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
