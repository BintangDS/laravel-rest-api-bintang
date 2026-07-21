<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    // GET /api/products (Public)
    public function index()
    {
        $products = Product::all();
        
        // ProductResource::collection automatically wraps the array in "data"
        return ProductResource::collection($products);
    }

    // GET /api/products/{id} (Public)
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        return new ProductResource($product);
    }

    // POST /api/products (Admin Only)
    public function store(Request $request)
    {
        // Validate the request based on the requirement rules
        $validated = $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'description' => 'nullable|string'
        ]);

        $product = Product::create($validated);

        return new ProductResource($product);
    }

    // PUT /api/products/{id} (Admin Only)
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        // Validate the request based on the requirement rules
        $validated = $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'description' => 'nullable|string'
        ]);

        $product->update($validated);

        return new ProductResource($product);
    }

    // DELETE /api/products/{id} (Admin Only)
    public function destroy(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'data' => [
                'message' => 'Product successfully deleted'
            ]
        ], 200);
    }
}