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

    protected $appends = ['attributes_data'];
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



    public function ProductAttr()
    {
        return $this->hasMany(ProductAttr::class);
    }


    // ... العلاقات الأخرى الموجودة ...

    public function getAttributesDataAttribute()
    {
        return $this->ProductAttr->map(function($item) {
            return [
                'attribute_id' => $item->attribute_id,
                'product_attributes_id' => $item->id,
                'name_attributes' => $item->Attribute->name,
                'value_attributes' => $item->value
            ];
        });
    }


        // في نموذج Product.php
    public function getActiveCategoryOffers()
    {
        return $this->subcategory->Category->adminOffers()
            ->where('status', 'active')
            ->where(function($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->orderBy('discount_percentage', 'desc')
            ->get();
    }

    public function getActiveSubCategoryOffers()
    {
        return $this->subcategory->adminOffers()
            ->where('status', 'active')
            ->where(function($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->orderBy('discount_percentage', 'desc')
            ->get();
    }

    public function getBestApplicableDiscount()
    {
        // أولاً نتحقق من وجود خصم على التصنيف الفرعي
        $subCategoryOffer = $this->getActiveSubCategoryOffers()->first();

        // ثم نتحقق من وجود خصم على الفئة الرئيسية
        $categoryOffer = $this->getActiveCategoryOffers()->first();

        // نرجع الخصم الأعلى قيمة
        if ($subCategoryOffer && $categoryOffer) {
            return $subCategoryOffer->discount_percentage > $categoryOffer->discount_percentage
                ? $subCategoryOffer
                : $categoryOffer;
        }

        return $subCategoryOffer ?: $categoryOffer;
    }

    public function getFinalPriceAttribute()
    {
        $originalPrice = $this->price;
        $bestOffer = $this->getBestApplicableDiscount();

        if ($bestOffer) {
            return $bestOffer->applyOffer($originalPrice);
        }

        return $originalPrice;
    }





}
