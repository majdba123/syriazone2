<?php

namespace App\Services\Vendor;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;

class UserVendorService
{
    public function createUserAndVendor(array $data)
    {
        // إنشاء المستخدم
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // إنشاء Vendor وربطه بالمستخدم
        $vendor = Vendor::create([
            'user_id' => $user->id,
        ]);

        return [
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'vendor_status' => $user->vendor->status,
        ];
    }


    private function formatResponse($vendor, $user = null, $message = '', $additionalData = [])
    {
        $response = [
            'vendor' => [
                'id' => $vendor->id,
                'status' => $vendor->status,
            ],
            'user' => [
                'id' => $user ? $user->id : $vendor->user->id,
                'name' => $user ? $user->name : $vendor->user->name,
                'email' => $user ? $user->email : $vendor->user->email,
            ],
            'message' => $message,
        ];

        return array_merge($response, $additionalData);
    }

    public function getVendorInfo($vendorId)
    {
        $vendor = vendor::findOrFail($vendorId);

        return $this->formatResponse($vendor, null, 'Vendor info retrieved successfully');
    }

    public function updateVendorAndUser($vendorId, array $data)
    {
        $vendor = vendor::findOrFail($vendorId);
        $user = $vendor->user;

        if (isset($data['name'])) {
            $user->name = $data['name'];
        }
        if (isset($data['email'])) {
            $user->email = $data['email'];
        }
        if (isset($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        $additionalData = [];
        if (isset($data['password'])) {
            $additionalData['password_updated'] = true;
        }

        return $this->formatResponse($vendor, $user, 'Vendor and user updated successfully', $additionalData);
    }

    public function updateVendorStatus($vendorId, $status)
    {
        $vendor = vendor::findOrFail($vendorId);
        $vendor->status = $status;
        $vendor->save();

        return $this->formatResponse($vendor, null, 'Vendor status updated successfully');
    }

    public function getVendorsByStatus($status, $perPage = 5)
    {
        $query = $status === 'all' ? vendor::query() : Vendor::where('status', $status);
        $vendors = $query->paginate($perPage);

        $formattedVendors = $vendors->map(function ($vendor) {
            return $this->formatResponse($vendor, $vendor->user);
        });

        return [
            'data' => $formattedVendors,
            'pagination' => [
                'current_page' => $vendors->currentPage(),
                'last_page' => $vendors->lastPage(),
                'per_page' => $vendors->perPage(),
                'total' => $vendors->total(),
            ],
            'message' => 'Vendors retrieved successfully',
        ];
    }
}
