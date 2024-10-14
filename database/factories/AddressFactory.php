<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition()
    {
        $turkmenCities = [
            'Ashgabat' => '744000',
            'Turkmenabat' => '746100',
            'Dashoguz' => '746000',
            'Mary' => '745400',
            'Balkanabat' => '745100',
        ];

        $city = $this->faker->randomElement(array_keys($turkmenCities));

        // Define districts with their respective numbers
        $districts = [
            'Parahat' => [3, 4, 5, 6, 7, 8],
            'Mir' => [1, 2, 3, 4, 5, 6],
            'mkr' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
        ];

        // Add Ashgabat-specific regions
        $ashgabatRegions = ['Koshi', 'Gurtly', '30 mkr'];

        // Randomly select a district type or Ashgabat-specific region
        if ($city === 'Ashgabat' && $this->faker->boolean(30)) { // 30% chance for Ashgabat-specific regions
            $districtType = $this->faker->randomElement($ashgabatRegions);
            $districtNumber = null;
        } else {
            $districtType = $this->faker->randomElement(array_keys($districts));
            $districtNumber = $this->faker->randomElement($districts[$districtType]);
        }

        // Generate random building number and optional apartment number
        $buildingNumber = $this->faker->numberBetween(1, 50);
        $apartmentNumber = $this->faker->optional(0.7)->numberBetween(1, 100);

        // Construct the address
        if (in_array($districtType, $ashgabatRegions)) {
            $address = "Ashgabat, {$districtType}, {$buildingNumber}";
        } elseif ($districtType === 'mkr') {
            $address = "{$city}, {$districtNumber} {$districtType}, {$buildingNumber}";
        } else {
            $address = "{$city}, {$districtType} {$districtNumber}/{$buildingNumber}";
        }

        if ($apartmentNumber) {
            $address .= ", apt. {$apartmentNumber}";
        }

        return [
            'shop_id' => Shop::factory(),
            'address_name' => $address,
            'postal_code' => $turkmenCities[$city],
        ];
    }
}