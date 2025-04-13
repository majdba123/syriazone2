<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category_Vendor extends Model
{
    use HasFactory;
    protected $fillable = [
        'vendor_id',
        'category_id',
        'percent',
        'status',


    ];
    public function vendor()
    {
        return $this->belongsTo(vendor::class,'vendor_id');
    }
    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }
}
