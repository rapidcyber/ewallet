<?php

namespace App\Filament\Exports;

use App\Models\Transaction;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class TransactionExporter extends Exporter
{
    protected static ?string $model = Transaction::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('sender_type'),
            ExportColumn::make('sender_id'),
            ExportColumn::make('recipient_type'),
            ExportColumn::make('recipient_id'),
            ExportColumn::make('txn_no'),
            ExportColumn::make('ref_no'),
            ExportColumn::make('currency'),
            ExportColumn::make('amount'),
            ExportColumn::make('service_fee'),
            ExportColumn::make('provider.name'),
            ExportColumn::make('channel.name'),
            ExportColumn::make('type.name'),
            ExportColumn::make('rate'),
            ExportColumn::make('extras'),
            ExportColumn::make('status.name'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('deleted_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your transaction export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
