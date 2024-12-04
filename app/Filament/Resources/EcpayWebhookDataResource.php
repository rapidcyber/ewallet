<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EcpayWebhookDataResource\Pages;
use App\Filament\Resources\EcpayWebhookDataResource\RelationManagers;
use App\Models\EcpayWebhookData;
use App\Tables\Columns\JsonDisplay;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EcpayWebhookDataResource extends Resource
{
    protected static ?string $model = EcpayWebhookData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Webhooks';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('data')
                    ->required(),
                Forms\Components\TextInput::make('env')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                JsonDisplay::make('data'),
                Tables\Columns\TextColumn::make('env')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageEcpayWebhookData::route('/'),
        ];
    }
}
