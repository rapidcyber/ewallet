<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductOrderResource\Pages;
use App\Filament\Resources\ProductOrderResource\RelationManagers;
use App\Models\ProductOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductOrderResource extends Resource
{
    protected static ?string $model = ProductOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Merchant Modules';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
                Forms\Components\TextInput::make('buyer_type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('buyer_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('shipping_fee')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('order_number')
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('delivery_type')
                    ->required(),
                Forms\Components\Select::make('warehouse_id')
                    ->relationship('warehouse', 'name'),
                Forms\Components\Select::make('shipping_option_id')
                    ->relationship('shipping_option', 'name')
                    ->required(),
                Forms\Components\Select::make('payment_option_id')
                    ->relationship('payment_option', 'name')
                    ->required(),
                Forms\Components\TextInput::make('tracking_number')
                    ->maxLength(16),
                Forms\Components\Select::make('shipping_status_id')
                    ->relationship('shipping_status', 'name')
                    ->required(),
                Forms\Components\DateTimePicker::make('processed_at'),
                Forms\Components\Textarea::make('termination_reason')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('cancelled_by'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('buyer_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('buyer_id'),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shipping_fee')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_number')
                    ->copyable()
                    ->copyMessage('Order number copied!')
                    ->copyMessageDuration(2000)
                    ->searchable(),
                Tables\Columns\TextColumn::make('tracking_number')
                    ->copyable()
                    ->copyMessage('Tracking number copied!')
                    ->copyMessageDuration(2000)
                    ->searchable(),
                Tables\Columns\TextColumn::make('delivery_type')
                    ->formatStateUsing(fn (string $state): string => ucwords($state)),
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->numeric(),
                Tables\Columns\TextColumn::make('shipping_option.name'),
                Tables\Columns\TextColumn::make('payment_option.name'),
                Tables\Columns\TextColumn::make('shipping_status.name')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Unpaid' => 'gray',
                        'Pending' => 'info',
                        'Packed' => 'info',
                        'Ready to Ship' => 'info',
                        'Shipping' => 'warning',
                        'Completed' => 'success',
                        'Cancellation' => 'danger',
                        'Failed Delivery' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('processed_at')
                    ->dateTime(null, 'Asia/Manila')
                    ->sortable(),
                Tables\Columns\TextColumn::make('termination_reason')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('cancelled_by')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('shipping_status')
                    ->relationship('shipping_status', 'name', fn (Builder $query): Builder => $query->orderBy('id', 'asc')),
                SelectFilter::make('payment_option')
                    ->relationship('payment_option', 'name'),
                SelectFilter::make('shipping_option')
                    ->relationship('shipping_option', 'name'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProductOrders::route('/'),
        ];
    }
}
