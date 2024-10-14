<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $table = 'sections';

    protected $fillable = [
        'name_tm',
        'name_en',
        'name_ru',
        'name_tr',
        'desc_tm',
        'desc_en',
        'desc_ru',
        'desc_tr',
        'image',
        'section_id',
    ];

    public function parent()
    {
        return $this->belongsTo(Section::class,'section_id', 'id');
    }

    public function getTopParent()
    {
        return $this->section_id;
    }

    public function sections()
    {
        return $this->hasMany(Section::class,'section_id', 'id');
    }

    public function childrenSections()
    {
        return $this->hasMany(Section::class)->with('sections');
    }

    protected function fillableData()
    {
        return $this->fillable;
    }
}
