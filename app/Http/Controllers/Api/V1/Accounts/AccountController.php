<?php

namespace App\Http\Controllers\Api\V1\Accounts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Client;
use App\Models\OrderProduct;
use App\Models\Product;
use Carbon\Carbon;
use App\Models\ClientDocument;
use App\Models\Employee;
use Illuminate\Support\Facades\Storage;
use DB;



class AccountController extends Controller
{
    public function getPurchase()
{
    try {
        $purchase = DB::table('purchases')
            ->leftJoin('providers', 'purchases.provider_id', '=', 'providers.id')
            ->leftJoin('raw_materials', 'purchases.item_id', '=', 'raw_materials.id')
            ->select(
                'purchases.id as purchase_id',
                'purchases.quantity',
                'purchases.price',
                'purchases.measure',
                'purchases.note',
                'purchases.expected_deliver',
                'purchases.statut',
                'providers.id as provider_id',
                'providers.name as provider_name',
                'raw_materials.id as raw_material_id',
                'raw_materials.name as raw_material_name'
            )
            ->orderBy('purchases.id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'Purchase data fetched successfully',
            'data' => $purchase
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'status_code' => 500,
            'message' => 'Something went wrong: ' . $e->getMessage()
        ]);
    }
}


    // =============================Supplier============================
   public function getDistributor()
{
    try {
        $suppliers = Client::with('documents')->get();

        // Replace file names with full URLs in the original fields
        $suppliers->transform(function ($client) {
            foreach ($client->documents as $doc) {
                $doc->document_image1 = $doc->document_image1 
                    ? asset('storage/' . $doc->document_image1) 
                    : null;
                $doc->document_image2 = $doc->document_image2 
                    ? asset('storage/' . $doc->document_image2) 
                    : null;
                $doc->document_image3 = $doc->document_image3 
                    ? asset('storage/' . $doc->document_image3) 
                    : null;
            }
            return $client;
        });

        return response()->json([
            'success' => true,
            'status_code' => 200,
            'data' => $suppliers
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'status_code' => 500,
            'message' => 'Something went wrong: ' . $e->getMessage()
        ]);
    }
}


// =====================Get Order List===================================
public function getOrder()
{
    try {
        $orders = DB::table('order_products')
            ->leftJoin('products', 'order_products.product_id', '=', 'products.id')
            ->leftJoin('employees', 'order_products.emp_id', '=', 'employees.id')
            ->select(
                'order_products.id as order_id',
                'order_products.product_quantity',
                'order_products.product_price',
                'order_products.other_customer',
                'order_products.customer',
                'order_products.status',
                'order_products.created_at',
                'products.id as product_id',
                'products.name as product_name',
                'products.image as product_image',
                'employees.id as employee_id',
                'employees.firstname as employee_firstname',
                'employees.phone as employee_phone'
            )
            ->orderBy('order_products.id', 'desc')
            ->get();

        // Attach full URL for product image
        $orders->transform(function ($order) {
            $order->product_image = $order->product_image 
                ? asset('storage' . $order->product_image)
                : null;
            return $order;
        });

        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'Order data fetched successfully',
            'data' => $orders
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'status_code' => 500,
            'message' => 'Something went wrong: ' . $e->getMessage()
        ]);
    }
}


}
