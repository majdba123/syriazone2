<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'token',
        'percent',
        'status',


    ];
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function coupon_order()
    {
        return $this->hasMany(Coupon_Order::class);
    }
}
