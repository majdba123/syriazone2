<?php

namespace App\Http\Controllers;

use App\Models\vendor;
use App\Models\VendorProfile;
use Illuminate\Http\Request;
use App\Services\Vendor\VendorDashboardService;
use Illuminate\Support\Facades\Auth;

class VendorProfileController extends Controller
{
    protected $dashboardService;

    public function __construct(VendorDashboardService $dashboardService )
    {
        $this->dashboardService = $dashboardService;

    }


    public function VendorDashboard( $vendor_id = null)
    {

        if ($vendor_id==null) {
            $user = Auth::user();
            // التحقق من وجود التاجر المرتبط بالمستخدم
            if (!$user || !$user->vendor) {
                return response()->json(['error' => 'Vendor not found for the current user.'], 403);
            }
            // جلب التاجر المرتبط
            $vendor = $user->vendor;

        }else{
            $vendor = Vendor::findOrFail($vendor_id);
        }

        return response()->json($this->dashboardService->getDashboardData($vendor));
    }


}
