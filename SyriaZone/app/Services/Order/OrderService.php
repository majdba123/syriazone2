<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\Product;
use App\Models\Order_Product;
use Illuminate\Support\Facades\Auth;
use App\Models\vendor;

class OrderService
{
    public function createOrder(array $validatedData)
    {
        $userId = Auth::id();
        // إنشاء الطلب
        $order = Order::create([
            'user_id' => $userId,
            'total_price' => 0, // سيتم تحديثه لاحقاً
            'status' => 'pending', // أو الحالة التي تريدها
        ]);

        // حساب سعر المنتجات وإضافتها إلى الطلب
        $totalPrice = 0;
        foreach ($validatedData['products'] as $productData) {
            $product = Product::find($productData['product_id']);
            $orderProduct = Order_Product::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $productData['quantity'],
                'total_price' => $product->price * $productData['quantity'],
            ]);
            $totalPrice += $orderProduct->total_price;
        }

        // تحديث سعر الطلب الكلي
        $order->update(['total_price' => $totalPrice]);

        return $order;
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
