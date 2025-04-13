<?php

namespace App\Services\Vendor;

use App\Models\Vendor;

class VendorDashboardService
{
    public function getDashboardData(Vendor $vendor)
    {
        return [
            'stats' => [
                'completed_orders' => $vendor->completed_orders_count,
                'pending_orders' => $vendor->pending_orders_count,
                'cancelled_orders' => $vendor->cancelled_orders_count,
                'total_sales_complete' => $vendor->total_sales,
                'total_sales_pending' => $vendor->total_sales_pending,
                'total_commissions_complete' => $vendor->total_commissions,
                'balance' => $vendor->total_sales - $vendor->total_commissions
            ],
        ];
    }
}
