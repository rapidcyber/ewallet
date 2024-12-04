<?php

namespace App\Filament\Resources\AllbankWebhookDataResource\Pages;

use App\Filament\Resources\AllbankWebhookDataResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAllbankWebhookData extends ManageRecords
{
    protected static string $resource = AllbankWebhookDataResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
