<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // public static function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             Forms\Components\TextInput::make('sender_type')
    //                 ->required()
    //                 ->maxLength(255),
    //             Forms\Components\TextInput::make('sender_id')
    //                 ->required()
    //                 ->numeric(),
    //             Forms\Components\TextInput::make('recipient_type')
    //                 ->required()
    //                 ->maxLength(255),
    //             Forms\Components\TextInput::make('recipient_id')
    //                 ->required()
    //                 ->numeric(),
    //             Forms\Components\TextInput::make('invoice_no')
    //                 ->required()
    //                 ->maxLength(255),
    //             Forms\Components\TextInput::make('currency')
    //                 ->required()
    //                 ->maxLength(255)
    //                 ->default('PHP'),
    //             Forms\Components\TextInput::make('message')
    //                 ->required()
    //                 ->maxLength(255),
    //             Forms\Components\DatePicker::make('due_date'),
    //             Forms\Components\TextInput::make('status')
    //                 ->required(),
    //             Forms\Components\TextInput::make('minimum_partial')
    //                 ->numeric(),
    //             Forms\Components\TextInput::make('type')
    //                 ->required(),
    //         ]);
    // }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sender_type'),
                Tables\Columns\TextColumn::make('sender_id')
                    ->numeric(),
                Tables\Columns\TextColumn::make('recipient_type'),
                Tables\Columns\TextColumn::make('recipient_id')
                    ->numeric(),
                Tables\Columns\TextColumn::make('invoice_no')
                    ->searchable(),
                Tables\Columns\TextColumn::make('currency')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('message')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucwords($state))
                    ->color(fn (string $state): string => match ($state) {
                        'unpaid' => 'gray',
                        'partial' => 'warning',
                        'paid' => 'success',
                    }),
                Tables\Columns\TextColumn::make('minimum_partial')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('type')
                    ->formatStateUsing(fn (string $state): string => ucwords($state)),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(null, 'Asia/Manila')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(null, 'Asia/Manila')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'paid' => 'Paid',
                        'unpaid' => 'Unpaid',
                        'partial' => 'Partial',
                    ]),
                SelectFilter::make('type')
                    ->options([
                        'payable' => 'Payable',
                        'quotation' => 'Quotation',
                    ])
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageInvoices::route('/'),
        ];
    }
}
