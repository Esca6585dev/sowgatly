<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => [
                'tm' => $this->name_tm,
                'en' => $this->name_en,
                'ru' => $this->name_ru,
            ],
            'image' => $this->image ? asset($this->image) : null,
            'category' => $this->when($this->parent, function () {
                return [
                    'name' => [
                        'tm' => $this->parent->name_tm,
                        'en' => $this->parent->name_en,
                        'ru' => $this->parent->name_ru,
                    ],
                    'created_at' => $this->parent->created_at->toDateTimeString(),
                    'updated_at' => $this->parent->updated_at->toDateTimeString(),
                ];
            }),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}