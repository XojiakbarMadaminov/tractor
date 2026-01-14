<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\TableWidget;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class UnsoldProductsList extends TableWidget
{
    use HasWidgetShield;

    protected static bool $isLazy = true;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Umuman sotilmagan tovarlar';

    protected function getTableQuery(): Builder
    {
        return Product::query()
            ->whereDoesntHave('saleItems');
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('index')->label('#')->rowIndex(),
            TextColumn::make('name')->label('Mahsulot nomi'),
            TextColumn::make('yuan_price')->label('Yuan narxi'),
            TextColumn::make('initial_price')->label('Kelgan narxi'),
            TextColumn::make('price')->label('Narxi'),
            TextColumn::make('created_at')->label('Yaratilgan sana')->sortable(),
        ];
    }
}
