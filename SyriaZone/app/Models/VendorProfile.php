<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorProfile extends Model
{
    use HasFactory;
    protected $fillable = [
        'vendor_id',
        "completed_orders",
        "pending_orders",
        "cancelled_orders",
        "total_sales_complete",
        "total_commissions_complete",

    ];

    public function vendor()
    {
        return $this->belongsTo(vendor::class,'vendor_id');
    }


}
