<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeRoleResource\Pages;
use App\Filament\Resources\EmployeeRoleResource\RelationManagers;
use App\Models\EmployeeRole;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeRoleResource extends Resource
{
    protected static ?string $model = EmployeeRole::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Access Control';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->live(onBlur: true, debounce: 200)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', str($state)->slug('_')->toString()))
                    ->maxLength(50),
                Forms\Components\Hidden::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
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
            'index' => Pages\ListEmployeeRoles::route('/'),
        ];
    }
}
