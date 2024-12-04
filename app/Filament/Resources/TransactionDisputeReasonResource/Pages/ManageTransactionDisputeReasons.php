<?php

namespace App\Filament\Resources\TransactionDisputeReasonResource\Pages;

use App\Filament\Resources\TransactionDisputeReasonResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTransactionDisputeReasons extends ManageRecords
{
    protected static string $resource = TransactionDisputeReasonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
