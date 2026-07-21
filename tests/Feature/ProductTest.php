<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test anyone can retrieve a list of products.
     */
    public function test_anyone_can_retrieve_products(): void
    {
        Product::create([
            'name' => 'Active Product',
            'description' => 'Active product description',
            'status' => 'active',
            'price' => 100.00,
        ]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'description', 'price', 'status']
                ]
            ]);
    }

    /**
     * Test anyone can view a single product.
     */
    public function test_anyone_can_view_single_product(): void
    {
        $product = Product::create([
            'name' => 'Sample Product',
            'description' => 'Sample product description',
            'price' => 50.00,
            'status' => 'active',
        ]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $product->id,
                    'name' => 'Sample Product',
                    'price' => '50.00',
                    'status' => 'active',
                ]
            ]);
    }

    /**
     * Test admin can create a product.
     */
    public function test_admin_can_create_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('admin_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/products', [
            'name' => 'New Product',
            'description' => 'A nice product',
            'price' => 120.50,
            'status' => 'active',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'New Product');

        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'price' => 120.50,
        ]);
    }

    /**
     * Test admin can update a product.
     */
    public function test_admin_can_update_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('admin_token')->plainTextToken;

        $product = Product::create([
            'name' => 'Old Product',
            'description' => 'Old description',
            'price' => 10.00,
            'status' => 'active',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/products/{$product->id}", [
            'name' => 'Updated Product',
            'price' => 99.99,
            'status' => 'inactive',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Product');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'status' => 'inactive',
        ]);
    }

    /**
     * Test admin can delete a product.
     */
    public function test_admin_can_delete_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('admin_token')->plainTextToken;

        $product = Product::create([
            'name' => 'To Be Deleted Product',
            'description' => 'To be deleted product description',
            'price' => 20.00,
            'status' => 'active',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'message' => 'Product successfully deleted'
                ]
            ]);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /**
     * Test regular user cannot manage products.
     */
    public function test_regular_user_cannot_manage_products(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('user_token')->plainTextToken;

        $product = Product::create([
            'name' => 'Normal Product',
            'description' => 'Normal product description',
            'price' => 15.00,
            'status' => 'active',
        ]);

        // Attempt Create
        $responseCreate = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/products', [
            'name' => 'Unauthorized Product',
            'price' => 10.00,
            'status' => 'active',
        ]);
        $responseCreate->assertStatus(403);

        // Attempt Update
        $responseUpdate = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/products/{$product->id}", [
            'name' => 'Hacked Product',
            'price' => 10.00,
            'status' => 'active',
        ]);
        $responseUpdate->assertStatus(403);

        // Attempt Delete
        $responseDelete = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/products/{$product->id}");
        $responseDelete->assertStatus(403);
    }
}
