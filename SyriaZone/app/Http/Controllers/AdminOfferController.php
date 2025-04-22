<?php

namespace App\Http\Controllers;

use App\Models\AdminOffer;
use App\Models\Category;
use App\Models\Sub_Categort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminOfferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = AdminOffer::with(['offerable', 'admin']);
        
        // فلترة حسب الحالة
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // فلترة حسب نوع العرض (category أو subcategory)
        if ($request->has('type')) {
            $query->where('offerable_type', $request->type == 'category' ? 'App\Models\Category' : 'App\Models\Sub_Categort');
        }
        
        // فلترة حسب تاريخ البداية
        if ($request->has('start_date_from')) {
            $query->where('start_date', '>=', $request->start_date_from);
        }
        
        if ($request->has('start_date_to')) {
            $query->where('start_date', '<=', $request->start_date_to);
        }
        
        // فلترة حسب تاريخ النهاية
        if ($request->has('end_date_from')) {
            $query->where('end_date', '>=', $request->end_date_from);
        }
        
        if ($request->has('end_date_to')) {
            $query->where('end_date', '<=', $request->end_date_to);
        }
        
        // ترتيب النتائج
        $query->orderBy('created_at', 'desc');
        
        $offers = $query->get();
        
        return response()->json(['offers' => $offers]);
    }
    
    // عرض خصومات فئة معينة
    public function getOffersByCategory($category_id)
    {
        $category = Category::find($category_id);
        
        if (!$category) {
            return response()->json(['message' => 'الفئة غير موجودة'], 404);
        }
        
        $offers = $category->adminOffers()
            ->with(['admin'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
            'category' => $category->title,
            'offers' => $offers
        ]);
    }
    
    // عرض خصومات تصنيف فرعي معين
    public function getOffersBySubCategory($subcategory_id)
    {
        $subcategory = Sub_Categort::find($subcategory_id);
        
        if (!$subcategory) {
            return response()->json(['message' => 'التصنيف الفرعي غير موجود'], 404);
        }
        
        $offers = $subcategory->adminOffers()
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
            'subcategory' => $subcategory->name,
            'category' => $subcategory->category->title,
            'offers' => $offers
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'offerable_type' => 'required|in:category,subcategory',
            'offerable_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_percentage' => 'required|numeric|min:1|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // التحقق من وجود الفئة أو التصنيف الفرعي
        if ($request->offerable_type === 'category') {
            $offerable = Category::find($request->offerable_id);
        } else {
            $offerable = Sub_Categort::find($request->offerable_id);
        }

        if (!$offerable) {
            return response()->json(['message' => 'الفئة أو التصنيف الفرعي غير موجود'], 404);
        }

        $offer = new AdminOffer();
        $offer->user_id = auth()->id(); // ID المسؤول الذي أنشأ الخصم
        $offer->discount_percentage = $request->discount_percentage;
        $offer->start_date = $request->start_date;
        $offer->end_date = $request->end_date;
        $offer->status = $request->status;

        $offerable->adminOffers()->save($offer);

        return response()->json([
            'message' => 'تم إنشاء الخصم بنجاح',
            'offer' => $offer
        ], 201);
    }



    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $offer = AdminOffer::with(['offerable', 'admin'])->find($id);

        if (!$offer) {
            return response()->json(['message' => 'الخصم غير موجود'], 404);
        }

        return response()->json(['offer' => $offer]);
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AdminOffer $adminOffer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $offer = AdminOffer::find($id);

        if (!$offer) {
            return response()->json(['message' => 'الخصم غير موجود'], 404);
        }

        $validator = Validator::make($request->all(), [
            'discount_percentage' => 'sometimes|numeric|min:1|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'sometimes|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $offer->update($request->all());

        return response()->json([
            'message' => 'تم تحديث الخصم بنجاح',
            'offer' => $offer
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $offer = AdminOffer::find($id);
        if (!$offer) {
            return response()->json(['message' => 'الخصم غير موجود'], 404);
        }
        $offer->delete();
        return response()->json(['message' => 'تم حذف الخصم بنجاح']);
    }


    
}
