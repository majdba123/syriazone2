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


    public function getVendorInfo($vendorId)
    {
        $vendor =Vendor::findOrfail($vendorId);


        return [
            'user_name' => $vendor->user->name,
            'user_email' => $vendor->user->email,
            'user_id' => $vendor->user->id,
            'vendor_id' => $vendor->user->id,
            'vendor_status' => $vendor->status,
        ];
    }




    public function updateVendorAndUser($vendorId, array $data)
    {
            // استدعاء الـ Vendor
            $vendor = Vendor::findOrFail($vendorId);
            // استدعاء المستخدم المرتبط بالـ Vendor
            $user = $vendor->user;
            // تحديث بيانات المستخدم
            if (isset($data['name'])) {
                $user->name = $data['name'];
            }
            if (isset($data['email'])) {
                $user->email = $data['email'];
            }
            if (isset($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            // حفظ التعديلات في سجل المستخدم
            $user->save();
              // إرجاع بيانات مخصصة
            return [
                'user_id' => $user->id,
                'vendor_id' => $vendor->id,
                'status' => $vendor->status,
                'name' => $user->name,
                'email' => $user->email,
                'password' => $data['password'] ?? 'Password not updated', // إرسال كلمة المرور الخام فقط إذا تم إرسالها
            ];
    }



    public function updateVendorStatus($vendorId, $status)
    {
        // استدعاء الـ Vendor باستخدام الـ ID
        $vendor = Vendor::findOrFail($vendorId);

        // تحديث الحالة
        $vendor->status = $status;

        // حفظ التعديلات
        $vendor->save();

        // إرجاع البيانات
        return [
            'vendor_id' => $vendor->id,
            'status' => $vendor->status,
            'message' => 'Vendor status updated successfully.',
        ];
    }


    public function getVendorsByStatus($status, $perPage = 5)
    {
        if ($status === 'all') {
            return Vendor::paginate($perPage);
        }

        return Vendor::where('status', $status)->paginate($perPage);
    }


}
