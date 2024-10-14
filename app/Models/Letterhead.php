<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Letterhead extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'company_name_tm', 
        'company_name_en', 
        'address_tm',
        'address_en',
        'phone_number_tm',
        'phone_number_en',
        'email_tm',
        'email_en',
        'image',
    ];

    public function user()
    {
        return $this->hasOne(User::class,'user_id', 'id');
    }

    public static function findOrCreate($user_id, $data)
    {
        $real = static::where('user_id', $user_id)->first();
        if (is_null($real)) {
            return static::create($data);
        } else {
            return $real->update($data);
        }
    }

    protected function fillableData()
    {
        return $this->fillable;
    }
}
