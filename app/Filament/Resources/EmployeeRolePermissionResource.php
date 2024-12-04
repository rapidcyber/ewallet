<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeRolePermissionResource\Pages;
use App\Filament\Resources\EmployeeRolePermissionResource\RelationManagers;
use App\Models\EmployeeRolePermission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeRolePermissionResource extends Resource
{
    protected static ?string $model = EmployeeRolePermission::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Access Control';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_role_id')
                    ->relationship('employee_role', 'name')
                    ->required(),
                Forms\Components\Select::make('module_id')
                    ->relationship('module', 'name')
                    ->required(),
                Forms\Components\Select::make('permission_id')
                    ->relationship('permission', 'action')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                
            })
            ->columns([
                Tables\Columns\TextColumn::make('module.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee_role.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('permission.action')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee_role_id')
                    ->relationship('employee_role', 'name')
                    ->multiple()
                    ->preload()
                    ->label('Employee Role'),
                Tables\Filters\SelectFilter::make('module_id')
                    ->relationship('module', 'name')
                    ->multiple()
                    ->preload()
                    ->label('Module'),
                Tables\Filters\SelectFilter::make('permission_id')
                    ->relationship('permission', 'action')
                    ->multiple()
                    ->preload()
                    ->label('Permission'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageEmployeeRolePermissions::route('/'),
        ];
    }
}
