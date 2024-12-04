<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserKycResource\Pages;
use App\Filament\Resources\UserKycResource\RelationManagers;
use App\Models\UserKyc;
use App\Tables\Columns\JsonDisplay;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserKycResource extends Resource
{
    protected static ?string $model = UserKyc::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('request_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('liveness_score')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('card_sanity_score')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('selfie_sanity_score')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('card_tampering_score')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('liveness_req_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('card_sanity_req_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('selfie_sanity_req_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('card_tampering_req_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('selfie_image_id')
                    ->required(),
                Forms\Components\TextInput::make('front_card_image_id')
                    ->required(),
                Forms\Components\TextInput::make('back_card_image_id')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('request_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('liveness_score')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('card_sanity_score')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('selfie_sanity_score')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('card_tampering_score')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('liveness_req_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('card_sanity_req_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('selfie_sanity_req_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('card_tampering_req_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('selfie_image_id'),
                Tables\Columns\TextColumn::make('front_card_image_id'),
                Tables\Columns\TextColumn::make('back_card_image_id'),
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
            ])
            ->bulkActions([

            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUserKycs::route('/'),
        ];
    }
}
