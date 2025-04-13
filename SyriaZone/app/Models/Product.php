<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'sub__categort_id',
        'vendor_id',
        'name',
        'discription',
        'price',
    ];
    public function subcategory()
    {
        return $this->belongsTo(Sub_Categort::class, 'sub__categort_id');
    }
    public function vendor()
    {
        return $this->belongsTo(vendor::class, 'vendor_id');
    }
    public function discount()
    {
        return $this->hasOne(Discount::class);
    }
    public function order_product()
    {
        return $this->hasMany(Order_Product::class);
    }

    public function images()
    {
        return $this->hasMany(ImagProduct::class);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->whereHas('subcategory', function ($q) use ($categoryId) {
            $q->where('category_id', $categoryId);
        });
    }

    public function scopeBySubCategory($query, $subCategoryId)
    {
        return $query->where('sub__categort_id', $subCategoryId);
    }





}
