<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Category;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make(__('Barchasi')),
        ];

        Category::query()
            ->scopes('active')
            ->orderBy('name')
            ->get()
            ->each(function (Category $category) use (&$tabs) {
                $tabs['category_' . $category->id] = Tab::make($category->name)
                    ->modifyQueryUsing(
                        fn (Builder $query): Builder => $query->where('category_id', $category->id)
                    );
            });

        return $tabs;
    }
}
