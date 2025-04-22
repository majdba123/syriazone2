<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\Product;
use App\Models\Order_Product;
use Illuminate\Support\Facades\Auth;
use App\Models\vendor;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder(array $validatedData)
    {
        DB::beginTransaction();

        try {
            $userId = Auth::id();

            // إنشاء الطلب
            $order = Order::create([
                'user_id' => $userId,
                'total_price' => 0,
                'status' => 'pending',

            ]);

            $totalPrice = 0;
            $orderProductsDetails = [];

            foreach ($validatedData['products'] as $productData) {
                $product = Product::with(['subcategory.Category', 'discount'])->find($productData['product_id']);

                if (!$product) {
                    throw new \Exception('المنتج غير موجود: ' . $productData['product_id']);
                }

                $originalPrice = $product->price;
                $discountApplied = false;
                $discountValue = 0;
                $discountType = null;
                $productPrice = $originalPrice;

                // تطبيق خصم المنتج المباشر إذا كان موجوداً وفعالاً
                if ($product->discount && $product->discount->isActive()) {
                    $discountApplied = true;
                    $discountValue = $product->discount->value;
                    $productPrice = $product->discount->calculateDiscountedPrice($originalPrice);
                    $discountType = 'product';
                } 
                // إذا لم يكن هناك خصم على المنتج مباشرة، نتحقق من الخصومات على الفئة أو التصنيف الفرعي
                else {
                    $bestOffer = $product->getBestApplicableDiscount();
                    if ($bestOffer) {
                        $discountApplied = true;
                        $discountValue = $bestOffer->discount_percentage;
                        $productPrice = $bestOffer->applyOffer($originalPrice);
                        $discountType = $bestOffer->offerable_type;
                    }
                }

                $orderProduct = Order_Product::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $productData['quantity'],
                    'total_price' => $productPrice * $productData['quantity'],
                    'status' => 'pending',
                ]);

                $totalPrice += $orderProduct->total_price;

                $orderProductsDetails[] = [
                    'id' => $orderProduct->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $productData['quantity'],
                    'original_unit_price' => $originalPrice,
                    'final_unit_price' => $productPrice,
                    'discount_applied' => $discountApplied,
                    'discount_value' => $discountValue,
                    'discount_type' => $discountType,
                    'total_price' => $productPrice * $productData['quantity'],
                ];
            }

            $order->update(['total_price' => $totalPrice]);

            $responseData = [
                'success' => true,
                'order' => [
                    'id' => $order->id,
                    'user_id' => $order->user_id,
                    'total_price' => $order->total_price,
                    'status' => $order->status,
                    'created_at' => $order->created_at,
                    'products' => $orderProductsDetails,
                ],
                'message' => 'تم إنشاء الطلب بنجاح',
            ];

            DB::commit();

            return response()->json($responseData);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'فشل في إنشاء الطلب: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function groupProductsByVendor(array $orderData)
    {
        $vendorsGroup = [];

        foreach ($orderData['products'] as $productData) {
            $product = Product::with('vendor')->find($productData['product_id']);

            if (!$product) {
                continue; // أو يمكنك رمي استثناء هنا
            }

            $vendorId = $product->vendor->id;

            if (!isset($vendorsGroup[$vendorId])) {
                $vendorsGroup[$vendorId] = [
                    'vendor_id' => $vendorId,
                    'vendor_name' => $product->vendor->name,
                    'products' => [],
                    'products_total' => 0
                ];
            }

            $productTotal = $product->price * $productData['quantity'];

            $vendorsGroup[$vendorId]['products'][] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $productData['quantity'],
                'unit_price' => $product->price,

            ];

            $vendorsGroup[$vendorId]['products_total'] += $productTotal;
        }

        return array_values($vendorsGroup);
    }

    public function getOrdersByPriceRange($minPrice, $maxPrice)
    {
        $orders = Order::whereBetween('total_price', [$minPrice, $maxPrice])
            ->with(['order_product:id,order_id,product_id', 'order_product.Product:id,name'])
            ->paginate(8); // تحديد عدد الطلبات في كل صفحة (10 طلبات)

        return response()->json(['orders' => $orders], 200);
    }

    public function getAllOrders()
    {
        return Order::with(['order_product:id,order_id,product_id', 'order_product.Product:id,name'])
            ->paginate(8); // تقسيم الطلبات إلى صفحات
    }



    public function getOrdersByStatus($status)
    {
        if ($status === 'all') {
            return $this->getAllOrders(); // استدعاء الدالة التي تسترجع جميع الطلبات
        }

        return Order::where('status', $status)
            ->with(['order_product:id,order_id,product_id', 'order_product.Product:id,name'])
            ->paginate(8); // تقسيم الطلبات إلى صفحات
    }




    public function getOrdersByProduct($productId)
    {
        $orders = Order_Product::where('product_id', $productId)
            ->with(['order:id,status,total_price,user_id', 'order.user:id,name,email'])
            ->paginate(8); // تحديد عدد الطلبات في كل صفحة (10 طلبات)

        return $orders;
    }

    public function getOrdersByUser($userId)
    {
        $orders = Order::where('user_id', $userId)
            ->with(['order_product:id,order_id,product_id,status,total_price', 'order_product.product:id,name,price'])
            ->paginate(8); // تحديد عدد الطلبات في كل صفحة (10 طلبات)

        return $orders;
    }

    public function getOrdersByCategory($categoryId)
    {
        $products = Product::byCategory($categoryId)->pluck('id');

        $orders = Order_Product::whereIn('product_id', $products)
            ->with(['order:id,status,user_id,total_price', 'order.user:id,name,email'])
            ->paginate(8); // تحديد عدد الطلبات في كل صفحة (10 طلبات)

        return $orders;
    }


    public function getOrdersBySubCategory($subCategoryId)
    {
        $products = Product::bySubCategory($subCategoryId)->pluck('id');

        $orders = Order_Product::whereIn('product_id', $products)
            ->with(['order:id,status,user_id,total_price', 'order.user:id,name,email'])
            ->paginate(8); // تحديد عدد الطلبات في كل صفحة (10 طلبات)

        return $orders;
    }







}
