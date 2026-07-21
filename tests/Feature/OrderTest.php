<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test anyone can place an order with active products.
     */
    public function test_anyone_can_place_order_with_active_products(): void
    {
        // Create active products
        $product1 = Product::create([
            'name' => 'Active Product 1',
            'price' => 50.00,
            'status' => 'active',
        ]);

        $product2 = Product::create([
            'name' => 'Active Product 2',
            'price' => 25.00,
            'status' => 'active',
        ]);

        // Post order request
        $response = $this->postJson('/api/orders', [
            'customer_name' => 'Budi',
            'customer_email' => 'budi@mail.com',
            'items' => [
                ['product_id' => $product1->id, 'qty' => 2], // 2 * 50 = 100
                ['product_id' => $product2->id, 'qty' => 3], // 3 * 25 = 75
            ]
        ]);

        // Assert response success, status, structure, and automatic price calculations
        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'customer_name' => 'Budi',
                    'customer_email' => 'budi@mail.com',
                    'total_price' => '175.00',
                    'items' => [
                        [
                            'product_id' => $product1->id,
                            'qty' => 2,
                            'price' => '50.00',
                            'subtotal' => '100.00',
                        ],
                        [
                            'product_id' => $product2->id,
                            'qty' => 3,
                            'price' => '25.00',
                            'subtotal' => '75.00',
                        ],
                    ]
                ]
            ]);

        // Assert order exists in database
        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Budi',
            'total_price' => 175.00,
        ]);
    }

    /**
     * Test placing an order fails if a product is inactive.
     */
    public function test_order_fails_if_product_is_inactive(): void
    {
        // Create an active product and an inactive product
        $activeProduct = Product::create([
            'name' => 'Active Product',
            'price' => 10.00,
            'status' => 'active',
        ]);

        $inactiveProduct = Product::create([
            'name' => 'Inactive Product',
            'price' => 20.00,
            'status' => 'inactive',
        ]);

        // Post order request with one inactive product
        $response = $this->postJson('/api/orders', [
            'customer_name' => 'Budi',
            'customer_email' => 'budi@mail.com',
            'items' => [
                ['product_id' => $activeProduct->id, 'qty' => 1],
                ['product_id' => $inactiveProduct->id, 'qty' => 1],
            ]
        ]);

        // Assert validation fails
        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'items.1.product_id'
                ]
            ]);

        // Assert no order was saved in database (rollback occurred)
        $this->assertDatabaseEmpty('orders');
    }

    /**
     * Test admin can view all orders.
     */
    public function test_admin_can_view_all_orders(): void
    {
        // Create an admin user and generate a token
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('admin_token')->plainTextToken;

        // Create a dummy order
        $order = Order::create([
            'customer_name' => 'Budi',
            'customer_email' => 'budi@mail.com',
            'status' => 'pending',
            'total_price' => 100.00,
        ]);

        // Request order list as admin
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/orders');

        // Assert response shape and status
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'customer_name', 'customer_email', 'status', 'total_price', 'items']
                ]
            ]);
    }

    /**
     * Test admin can view a single order.
     */
    public function test_admin_can_view_single_order(): void
    {
        // Create admin user and token
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('admin_token')->plainTextToken;

        // Create a dummy order
        $order = Order::create([
            'customer_name' => 'Budi',
            'customer_email' => 'budi@mail.com',
            'status' => 'pending',
            'total_price' => 100.00,
        ]);

        // Request single order details
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/orders/{$order->id}");

        // Assert response status and correct customer name
        $response->assertStatus(200)
            ->assertJsonPath('data.customer_name', 'Budi');
    }

    /**
     * Test regular user cannot retrieve order list or single order.
     */
    public function test_regular_user_cannot_access_admin_order_endpoints(): void
    {
        // Create a regular user and token
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('user_token')->plainTextToken;

        // Create a dummy order
        $order = Order::create([
            'customer_name' => 'Budi',
            'customer_email' => 'budi@mail.com',
            'status' => 'pending',
            'total_price' => 100.00,
        ]);

        // Attempt order listing as regular user
        $responseList = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/orders');
        $responseList->assertStatus(403);

        // Attempt single order details as regular user
        $responseSingle = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/orders/{$order->id}");
        $responseSingle->assertStatus(403);
    }
}
