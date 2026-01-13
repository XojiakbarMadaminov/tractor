<?php

namespace App\Filament\Resources\Locations\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Joylashuv ma‘lumotlari')
                ->columnSpanFull()
                ->schema([
                    TextInput::make('name')
                        ->label('Nomi')
                        ->required()
                        ->unique('locations', 'name', ignoreRecord: true)
                        ->maxLength(255),
                    Toggle::make('is_active')
                        ->label('Aktiv')
                        ->default(true),
                ]),
        ]);
    }
}
