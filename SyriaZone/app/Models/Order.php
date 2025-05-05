<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'status',
        'total_price',


    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function order_product()
    {
        return $this->hasMany(Order_Product::class);
    }
// في نموذج Order
public function coupons()
{
    return $this->belongsToMany(Coupon::class, 'coupon__orders')
                ->withPivot('discount_amount')
                ->withTimestamps();
}

public function applyCoupon(Coupon $coupon)
{
    if (!$coupon->isActive()) {
        return false;
    }

    $discountAmount = $this->total_price * ($coupon->discount_percent / 100);

    $this->coupons()->attach($coupon, [
        'discount_amount' => $discountAmount
    ]);

    $this->total_price -= $discountAmount;
    $this->save();

    return true;
}
}
