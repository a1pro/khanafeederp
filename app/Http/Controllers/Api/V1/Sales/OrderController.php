<?php

namespace App\Http\Controllers\Api\V1\Sales;

use App\Http\Controllers\Controller; // correct import
use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use DB;
use App\Models\Product;
use App\Models\OrderProduct;
use Illuminate\Support\Str;
 
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller // ✅ Fixed here
{
   public function product_list(Request $request)
{
    DB::beginTransaction();
    try {
        $categoryId = $request->query('category_id');

        $query = Product::select('id', 'code', 'price', 'name', 'image', 'category_id')
            ->with(['category' => function($query) {
                $query->select('id', 'name');
            }])
            ->where('is_active', 1);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->get();

        $formattedProducts = $products->map(function ($product) {
            return [
                'id'            => $product->id,
                'code'          => $product->code,
                'price'         => $product->price,
                'name'          => $product->name,
                'image'         => $product->image ? asset('storage/' . $product->image) : null,
                'category_id'   => $product->category_id,
                'category_name' => $product->category->name ?? null
            ];
        });

        DB::commit();

        return response()->json([
            'success'     => true,
            'status_code' => 200,
            'message'     => 'Products fetched successfully',
            'data'        => $formattedProducts
        ]);
    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'success'     => false,
            'status_code' => 500,
            'message'     => 'Something went wrong: ' . $e->getMessage()
        ]);
    }
}

    public function order_place(Request $request)
    {
        if (!Auth::guard('employee')->check()) {
            return response()->json([
                'success'     => false,
                'status_code' => 401,
                'message'     => 'Employee not authenticated',
            ]);
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'customer' => 'nullable|string',
            'other_customer' => 'nullable|string|required_if:customer,other',
            'product_id' => 'required|array|min:1',
            'product_id.*' => 'required|string',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|integer|min:1',
            'price' => 'required|array|min:1',
            'price.*' => 'required|numeric|min:0',
        ], [
            'product_id.required' => 'At least one product is required',
            'quantity.required' => 'Quantities are required',
            'price.required' => 'Prices are required',
        ]);

        // Additional validation to ensure arrays exist and have same length
        $validator->after(function ($validator) use ($request) {
            // Check if arrays exist before counting
            if (is_null($request->product_id) || is_null($request->quantity) || is_null($request->price)) {
                $validator->errors()->add('products', 'Product IDs, quantities and prices are required');
                return;
            }

            // Now safe to count since we've verified they're not null
            if (count($request->product_id) !== count($request->quantity) || 
                count($request->product_id) !== count($request->price)) {
                $validator->errors()->add('products', 'Product IDs, quantities and prices must have the same number of items');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status_code' => 422,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ]);
        }

        DB::beginTransaction();

        try {
            $employee = Auth::guard('employee')->user();
            $orderId = 'ORD-' . Str::upper(Str::random(6)) . '-' . time();
            
            $customer = $request->customer === 'other' ? $request->other_customer : $request->customer;

            $products = [];
            foreach ($request->product_id as $index => $productId) {
                $products[] = [
                    'emp_id' => $employee->id,
                    'customer' => $customer,
                    'other_customer' => $request->customer === 'other' ? $request->other_customer : null,
                    'product_id' => $productId,
                    'product_quantity' => $request->quantity[$index],
                    'product_price' => $request->price[$index],
                    'order_id' => $orderId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Insert all products at once
            OrderProduct::insert($products);

            DB::commit();

            return response()->json([
                'success' => true,
                'status_code' => 201,
                'message' => 'Order placed successfully',
                'data' => [
                    'order_id' => $orderId,
                    'products' => $products,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'status_code' => 500,
                'message' => 'Failed to place order',
                'error' => $e->getMessage(),
            ]);
        }
    }
}


