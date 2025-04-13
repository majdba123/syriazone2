<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'percent',


    ];
    public function sub_category()
    {
        return $this->hasMany(Sub_Categort::class);
    }
    public function Category_Vendor()
    {
        return $this->hasMany(Category_Vendor::class);
    }

}
