<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Product;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = collect([
            'Xitoy',
            'Namangan',
            'Qo`qon',
        ])->mapWithKeys(function (string $name) {
            $location = Location::firstOrCreate(['name' => $name], ['is_active' => true]);

            return [$name => $location];
        });

        if ($xitoy = $locations->get('Xitoy')) {
            Product::query()->update(['is_from' => $xitoy->id]);
        }
    }
}
