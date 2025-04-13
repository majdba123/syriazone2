<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;
    protected $fillable = [
        'vendor_id',
        'product_id',
        'percent',
        'status',


    ];
    public function vendor()
    {
        return $this->belongsTo(vendor::class,'vendor_id');
    }
    public function Product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }
}
