<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * جلب معلومات المستخدم الحالي
     *
     * @return JsonResponse
     */
    public function getProfile(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'المستخدم غير مسجل الدخول'
                ], 401);
            }

            $response = [
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'type' => $user->type,
                    'location' => [
                        'lat' => $user->lat,
                        'lang' => $user->lang
                    ]
                ]
            ];

            // إضافة رابط الصورة إذا كانت موجودة
            if ($user->image) {
                $response['data']['image'] = Storage::url($user->image);
            }

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب بيانات المستخدم: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * تحديث معلومات المستخدم
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'المستخدم غير مسجل الدخول'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,'.$user->id,
                'phone' => 'sometimes|string|max:20',
                'lat' => 'sometimes|numeric',
                'lang' => 'sometimes|numeric',
                'password' => 'sometimes|string|min:8|confirmed',
                'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048' // 2MB كحد أقصى
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->only(['name', 'email', 'phone', 'lat', 'lang']);

            if ($request->has('password')) {
                $data['password'] = bcrypt($request->password);
            }

            // معالجة رفع الصورة
            if ($request->hasFile('image')) {
                // حذف الصورة القديمة إذا كانت موجودة
                if ($user->image && Storage::exists($user->image)) {
                    Storage::delete($user->image);
                }

                // حفظ الصورة الجديدة
                $path = $request->file('image')->store('public/users/images');
                $data['image'] = $path;
            }

            $user->update($data);

            $response = [
                'success' => true,
                'message' => 'تم تحديث البيانات بنجاح',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'location' => [
                        'lat' => $user->lat,
                        'lang' => $user->lang
                    ]
                ]
            ];

            // إضافة رابط الصورة إذا كانت موجودة
            if ($user->image) {
                $response['data']['image'] = Storage::url($user->image);
            }

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في تحديث البيانات: ' . $e->getMessage()
            ], 500);
        }

    }

}
