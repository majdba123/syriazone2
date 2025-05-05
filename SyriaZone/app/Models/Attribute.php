<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sub__categort_id'
    ];


    public function subcategory()
    {
        return $this->belongsTo(Sub_Categort::class, 'sub__categort_id');
    }

    public function ProductAttr()
    {
        return $this->hasMany(ProductAttr::class);
    }
}
