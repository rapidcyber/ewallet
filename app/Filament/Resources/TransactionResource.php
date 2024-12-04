<?php

namespace App\Filament\Resources;

use App\Filament\Exports\TransactionExporter;
use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Tables\Columns\JsonDisplay;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('sender_type'),
                Tables\Columns\TextColumn::make('sender_id'),
                Tables\Columns\TextColumn::make('recipient_type'),
                Tables\Columns\TextColumn::make('recipient_id'),
                Tables\Columns\TextColumn::make('txn_no')
                    ->label('Transaction number')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Transaction number copied!')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('ref_no')
                    ->label('Reference number')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Reference number copied!')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('currency'),
                Tables\Columns\TextColumn::make('amount'),
                Tables\Columns\TextColumn::make('service_fee'),
                Tables\Columns\TextColumn::make('provider.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('channel.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rate')
                    ->toggleable(isToggledHiddenByDefault: true),
                JsonDisplay::make('extras')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status.name')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Pending' => 'gray',
                        'Refunded' => 'warning',
                        'Successful' => 'success',
                        'Failed' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->relationship('type', 'name'),
                SelectFilter::make('channel')
                    ->relationship('channel', 'name'),
                SelectFilter::make('provider')
                    ->relationship('provider', 'name'),
                SelectFilter::make('status')
                    ->relationship('status', 'name'),
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                ExportBulkAction::make()
                    ->exporter(TransactionExporter::class),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
        ];
    }
}
