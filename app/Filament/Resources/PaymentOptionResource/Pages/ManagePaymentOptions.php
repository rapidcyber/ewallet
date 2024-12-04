<?php

namespace App\Filament\Resources\PaymentOptionResource\Pages;

use App\Filament\Resources\PaymentOptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePaymentOptions extends ManageRecords
{
    protected static string $resource = PaymentOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
