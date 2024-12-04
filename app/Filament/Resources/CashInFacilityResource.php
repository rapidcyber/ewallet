<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashInFacilityResource\Pages;
use App\Models\CashInFacility;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CashInFacilityResource extends Resource
{
    protected static ?string $model = CashInFacility::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'System';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->live(onBlur: true, debounce: 200)
                    ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', str($state)->slug('_')->toString()))
                    ->maxLength(255),
                Forms\Components\Checkbox::make('active'),
                Forms\Components\Hidden::make('slug')->unique(ignoreRecord: true)->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('active'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListCashInFacilities::route('/'),
            'create' => Pages\CreateCashInFacility::route('/create'),
            'view' => Pages\ViewCashInFacility::route('/{record}'),
            'edit' => Pages\EditCashInFacility::route('/{record}/edit'),
        ];
    }
}
