<?php

namespace App\Filament\Resources\EcpayWebhookDataResource\Pages;

use App\Filament\Resources\EcpayWebhookDataResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageEcpayWebhookData extends ManageRecords
{
    protected static string $resource = EcpayWebhookDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }
}
