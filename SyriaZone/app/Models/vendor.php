<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',


    ];
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function discount()
    {
        return $this->hasMany(Discount::class);
    }
    public function category_vendor()
    {
        return $this->hasMany(Category_Vendor::class);
    }
    public function product()
    {
        return $this->hasMany(Product::class);
    }

    public function orders()
    {
        return $this->hasManyThrough(
            Order_Product::class, // النموذج الوسيط
            Product::class,       // النموذج المرتبط
            'vendor_id',          // المفتاح الأجنبي في جدول المنتجات
            'product_id',         // المفتاح الأجنبي في جدول Order_Product
            'id',                 // المفتاح الأساسي في جدول Vendor
            'id'                  // المفتاح الأساسي في جدول Product
        );
    }


    public function vendor_profile()
    {
        return $this->hasOne(VendorProfile::class);
    }



    public function getCompletedOrdersCountAttribute()
    {
        return $this->orders()->where('order__products.status', 'complete')->count();
    }

    public function getPendingOrdersCountAttribute()
    {
        return $this->orders()->where('order__products.status', 'pending')->count();
    }

    public function getCancelledOrdersCountAttribute()
    {
        return $this->orders()->where('order__products.status', 'cancelled')->count();
    }

    public function getTotalSalesAttribute()
    {
        return $this->orders()->where('order__products.status', 'complete')->sum('order__products.total_price');
    }

    public function getTotalSalesPendingAttribute()
    {
        return $this->orders()->where('order__products.status', 'pending')->sum('order__products.total_price');
    }

    public function getTotalCommissionsAttribute()
    {
        return $this->orders()->where('order__products.status', 'complete')
            ->with(['product.subcategory.Category'])
            ->get()
            ->sum(function($order) {
                $rate = $order->product->subcategory->category->percent / 100;
                return $order->total_price * $rate;
            });
    }



    public function contacts()
    {
        return $this->morphMany(Contact::class, 'contactable');
    }

}
