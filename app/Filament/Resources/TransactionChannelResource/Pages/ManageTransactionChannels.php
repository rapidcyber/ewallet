<?php

namespace App\Filament\Resources\TransactionChannelResource\Pages;

use App\Filament\Resources\TransactionChannelResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTransactionChannels extends ManageRecords
{
    protected static string $resource = TransactionChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
