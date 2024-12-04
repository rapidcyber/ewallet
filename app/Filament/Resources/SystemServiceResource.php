<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SystemServiceResource\Pages;
use App\Models\SystemService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SystemServiceResource extends Resource
{
    protected static ?string $model = SystemService::class;

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
                Forms\Components\Select::make('availability')
                    ->options([
                        'active' => 'Active',
                        'soon' => 'Available Soon',
                        'maintenance' => 'Under Maintenance',
                    ]),
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
                Tables\Columns\TextColumn::make('availability')
                    ->searchable(),
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
            'index' => Pages\ListSystemServices::route('/'),
            'create' => Pages\CreateSystemService::route('/create'),
            'view' => Pages\ViewSystemService::route('/{record}'),
            'edit' => Pages\EditSystemService::route('/{record}/edit'),
        ];
    }
}
