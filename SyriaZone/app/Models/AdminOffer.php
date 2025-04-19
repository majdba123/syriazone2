<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminOffer extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'offerable_id',
        'offerable_type',
        'user_id',
        'status',
        'start_date',
        'end_date',
        'discount_percentage',
    ];

    // العلاقة البوليمورفيك
    public function offerable()
    {
        return $this->morphTo();
    }

    // العلاقة مع المسؤول (الإدمن)
    public function admin()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isActive()
    {
        if (strtolower($this->status) !== 'active') {
            return false;
        }

        $now = now();

        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    public function applyOffer($originalPrice)
    {
        if (!$this->isActive()) {
            return $originalPrice;
        }

        $discountAmount = $originalPrice * ($this->discount_percentage / 100);
        $finalPrice = $originalPrice - $discountAmount;

        return max($finalPrice, 0);
    }
}