<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="BannerResource",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="object", @OA\Property(property="tm", type="string"), @OA\Property(property="ru", type="string"), @OA\Property(property="en", type="string")),
 *     @OA\Property(property="subtitle", type="object", nullable=true, @OA\Property(property="tm", type="string"), @OA\Property(property="ru", type="string"), @OA\Property(property="en", type="string")),
 *     @OA\Property(property="image", type="string", nullable=true, example="https://sowgatly.app/storage/banners/x.jpg"),
 *     @OA\Property(property="link_type", type="string", enum={"none","category","product","shop","url"}),
 *     @OA\Property(property="link_value", type="string", nullable=true, example="3"),
 *     @OA\Property(property="position", type="integer", example=0)
 * )
 */
class BannerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => [
                'tm' => $this->title_tm,
                'ru' => $this->title_ru ?? $this->title_tm,
                'en' => $this->title_en ?? $this->title_tm,
            ],
            'subtitle' => [
                'tm' => $this->subtitle_tm,
                'ru' => $this->subtitle_ru ?? $this->subtitle_tm,
                'en' => $this->subtitle_en ?? $this->subtitle_tm,
            ],
            'image' => $this->image ? asset($this->image) : null,
            'link_type' => $this->link_type,
            'link_value' => $this->link_value,
            'position' => $this->position,
        ];
    }
}
