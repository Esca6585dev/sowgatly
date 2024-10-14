<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Region;
use App\Models\Address;
use Illuminate\Support\Facades\DB;

class RegionSeeder extends Seeder
{
    private $postalCode = 744000;

    public function run()
    {
        DB::transaction(function () {
            $turkmenistan = $this->createCountry();
            $this->createProvinces($turkmenistan);
            $this->createAshgabat($turkmenistan);
        });
    }

    private function createCountry(): Region
    {
        return Region::create([
            'name' => 'Turkmenistan',
            'type' => 'country',
        ]);
    }

    private function createProvinces(Region $country): void
    {
        $provinces = ['Ahal', 'Balkan', 'Dashoguz', 'Lebap', 'Mary'];

        foreach ($provinces as $provinceName) {
            $province = $this->createRegion($provinceName, $country->id, 'province');
            $this->createCitiesAndVillages($province);
        }
    }

    private function createCitiesAndVillages(Region $province): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $city = $this->createRegion("{$province->name} City {$i}", $province->id, 'city');

            for ($j = 1; $j <= 2; $j++) {
                $this->createRegion("{$city->name} Village {$j}", $city->id, 'village');
            }
        }
    }

    private function createAshgabat(Region $country): void
    {
        $this->createRegion('Ashgabat', $country->id, 'city');
    }

    private function createRegion(string $name, int $parentId, string $type): Region
    {
        return Region::create([
            'name' => $name,
            'parent_id' => $parentId,
            'type' => $type,
        ]);
    }

    private function createAddress(string $regionName, string $type): int
    {
        return Address::create([
            'street' => 'Main Street',
            'settlement' => $type === 'village' ? $regionName : null,
            'district' => $type === 'city' ? $regionName : null,
            'province' => $type === 'province' ? $regionName : null,
            'region' => $type === 'country' ? $regionName : 'Turkmenistan',
            'country' => 'Turkmenistan',
            'postal_code' => $this->getNextPostalCode()
        ])->id;
    }

    private function getNextPostalCode(): string
    {
        return (string) $this->postalCode++;
    }
}