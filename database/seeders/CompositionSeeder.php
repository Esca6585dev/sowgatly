
<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Composition;

class CompositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $compositions = [
            ['name' => 'Symphony No. 5'],
            ['name' => 'Moonlight Sonata'],
            ['name' => 'The Four Seasons'],
            ['name' => 'Eine kleine Nachtmusik'],
            ['name' => 'Toccata and Fugue in D minor'],
            ['name' => 'The Planets'],
            ['name' => 'Rhapsody in Blue'],
            ['name' => 'Canon in D'],
            ['name' => 'The Nutcracker'],
            ['name' => 'Für Elise'],
        ];

        foreach ($compositions as $composition) {
            Composition::create($composition);
        }
    }
}