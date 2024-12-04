<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionProviderResource\Pages;
use App\Filament\Resources\TransactionProviderResource\RelationManagers;
use App\Models\TransactionProvider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionProviderResource extends Resource
{
    protected static ?string $model = TransactionProvider::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->live(onBlur: true, debounce: 200)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', str($state)->slug('_')->toString()))
                    ->maxLength(255),
                Forms\Components\Hidden::make('slug')
                    ->required(),
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(3),
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
                Tables\Columns\TextColumn::make('code')
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ChannelsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTransactionProviders::route('/'),
            'edit' => Pages\EditTransactionProvider::route('/{record}/edit'),
        ];
    }
}
