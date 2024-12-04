<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Filament\Resources\MediaResource\RelationManagers;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

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
                Tables\Columns\TextColumn::make('model_type'),
                Tables\Columns\TextColumn::make('model_id'),
                Tables\Columns\TextColumn::make('collection_name'),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('file_name'),
                Tables\Columns\TextColumn::make('mime_type'),
                Tables\Columns\TextColumn::make('disk'),
                Tables\Columns\TextColumn::make('conversions_disk'),
                Tables\Columns\TextColumn::make('size'),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('model_type')
                    ->options(fn() => Media::distinct()->pluck('model_type', 'model_type'))
                    ->label('Model Type'),
                Tables\Filters\SelectFilter::make('model_id')
                    ->label('Model ID')
                    ->options(fn() => Media::distinct()->pluck('model_id', 'model_id'))
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
            'index' => Pages\ManageMedia::route('/'),
        ];
    }
}
