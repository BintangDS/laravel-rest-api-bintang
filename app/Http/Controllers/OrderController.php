<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Display a listing of orders (Admin Only).
     */
    public function index()
    {
        // Eager load items and their associated products
        $orders = Order::with('items.product')->get();
        return OrderResource::collection($orders);
    }

    /**
     * Display the specified order (Admin Only).
     */
    public function show($id)
    {
        $order = Order::with('items.product')->find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found'
            ], 404);
        }

        return new OrderResource($order);
    }

    /**
     * Store a newly created order in storage (Public).
     */
    public function store(Request $request)
    {
        // 1. Validate request payload
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Process order within DB Transaction
        return DB::transaction(function () use ($request) {
            $totalPrice = 0;
            $itemsData = [];

            foreach ($request->items as $index => $item) {
                $product = Product::find($item['product_id']);

                // Ensure the product is active
                if ($product->status !== 'active') {
                    return response()->json([
                        'message' => 'Validation failed',
                        'errors' => [
                            "items.{$index}.product_id" => ["Product '{$product->name}' is inactive and cannot be ordered."]
                        ]
                    ], 422);
                }

                // Subtotal = qty * price (snapshot price)
                $subtotal = $item['qty'] * $product->price;
                $totalPrice += $subtotal;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'qty' => $item['qty'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            // Create the order record
            $order = Order::create([
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'status' => 'pending',
                'total_price' => $totalPrice,
            ]);

            // Save order items
            foreach ($itemsData as $itemData) {
                $order->items()->create($itemData);
            }

            // Eager load relations for response
            $order->load('items.product');

            return (new OrderResource($order))
                ->response()
                ->setStatusCode(201);
        });
    }
}
