<?php

namespace App\Filament\Resources\BalanceLimitResource\Pages;

use App\Filament\Resources\BalanceLimitResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageBalanceLimits extends ManageRecords
{
    protected static string $resource = BalanceLimitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
