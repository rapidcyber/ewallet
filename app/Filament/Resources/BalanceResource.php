<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BalanceResource\Pages;
use App\Filament\Resources\BalanceResource\RelationManagers;
use App\Models\Balance;
use App\Models\Merchant;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BalanceResource extends Resource
{
    protected static ?string $model = Balance::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('entity_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('entity_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction.txn_no')
                    ->label('Transaction Number'),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('entity_type')
                    ->options([
                        User::class => 'User',
                        Merchant::class => 'Merchant',
                    ])
                    ->label('Entity Type'),
                Tables\Filters\SelectFilter::make('entity_id')
                    ->label('Entity ID')
                    ->options(fn() => Balance::distinct()->pluck('entity_id', 'entity_id'))
                    ->searchable(),
            ])
            ->actions([

            ])
            ->bulkActions([

            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageBalances::route('/'),
        ];
    }
}
