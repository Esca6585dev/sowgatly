<?php

namespace Database\Factories;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

class BannerFactory extends Factory
{
    protected $model = Banner::class;

    public function definition(): array
    {
        return [
            'title_tm' => 'Täze ýyl',
            'title_ru' => 'С Новым годом',
            'title_en' => 'Happy New Year',
            'subtitle_tm' => 'Bagtly pursatlary sowgat ediň',
            'subtitle_ru' => 'Дарите моменты счастья',
            'subtitle_en' => 'Give moments of happiness',
            'image' => null,
            'link_type' => 'none',
            'link_value' => null,
            'region_id' => null,
            'position' => 0,
            'is_active' => true,
            'starts_at' => null,
            'ends_at' => null,
        ];
    }
}
