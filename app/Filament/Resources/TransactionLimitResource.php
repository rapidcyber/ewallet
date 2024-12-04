<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionLimitResource\Pages;
use App\Filament\Resources\TransactionLimitResource\RelationManagers;
use App\Models\TransactionLimit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionLimitResource extends Resource
{
    protected static ?string $model = TransactionLimit::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('transaction_type_id')
                    ->relationship('transaction_type', 'name')
                    ->required(),
                Forms\Components\Select::make('role_id')
                    ->relationship('role', 'name')
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('scope')
                    ->options([
                        'monthly' => 'Monthly',
                        'daily' => 'Daily',
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_type.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('role.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric(),
                Tables\Columns\TextColumn::make('scope')
                    ->formatStateUsing(fn (string $state): string => ucwords($state)),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactionLimits::route('/'),
        ];
    }
}
