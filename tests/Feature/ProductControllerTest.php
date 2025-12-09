<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product_with_stocks_and_images(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        $token = $this->postJson('/api/auth/signin', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()->json('access_token');

        $payload = [
            'name' => 'Test Product',
            'barcode' => 'TP-001',
            'category' => 'Test',
            'stocks' => [
                [
                    'sku' => 'SKU-1',
                    'quantity' => 5,
                    'sale_price' => 19.99,
                    'purchase_price' => 10.0,
                    'image' => UploadedFile::fake()->image('sku1.jpg'),
                ],
            ],
        ];

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->post('/api/products', $payload);

        $response->assertStatus(201);

        $data = $response->json('data');

        $this->assertEquals('Test Product', $data['name']);
        $this->assertArrayHasKey('stocks', $data);
        $this->assertCount(1, $data['stocks']);

        $stockImage = $data['stocks'][0]['image_url'];
        $this->assertNotNull($stockImage);

        $this->assertTrue(Storage::disk('public')->exists($stockImage));
    }

    public function test_admin_can_update_product_and_sync_stocks_and_images(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        $token = $this->postJson('/api/auth/signin', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()->json('access_token');

        // Create initial product with two stocks
        $createPayload = [
            'name' => 'Update Product',
            'barcode' => 'UP-001',
            'stocks' => [
                [
                    'sku' => 'UP-SKU-1',
                    'quantity' => 2,
                    'image' => UploadedFile::fake()->image('up1.jpg'),
                ],
                [
                    'sku' => 'UP-SKU-2',
                    'quantity' => 3,
                    'image' => UploadedFile::fake()->image('up2.jpg'),
                ],
            ],
        ];

        $createResp = $this->withHeader('Authorization', 'Bearer '.$token)
            ->post('/api/products', $createPayload);

        $createResp->assertStatus(201);
        $created = $createResp->json('data');

        $productId = $created['id'];
        $this->assertCount(2, $created['stocks']);

        $stock1 = $created['stocks'][0];
        $stock2 = $created['stocks'][1];

        $oldStock2Image = $stock2['image_url'];
        $this->assertTrue(Storage::disk('public')->exists($oldStock2Image));

        // Prepare update: modify stock1 quantity, remove stock2 (omit it), and add a new stock
        $updatePayload = [
            'name' => 'Update Product',
            'barcode' => 'UP-001',
            'stocks' => [
                [
                    'id' => $stock1['id'],
                    'sku' => $stock1['sku'],
                    'quantity' => 10,
                ],
                [
                    'sku' => 'UP-SKU-3',
                    'quantity' => 7,
                    'image' => UploadedFile::fake()->image('up3.jpg'),
                ],
            ],
        ];

        $updateResp = $this->withHeader('Authorization', 'Bearer '.$token)
            ->put("/api/products/{$productId}", $updatePayload);

        $updateResp->assertOk();

        $updated = $updateResp->json('data');

        // stock1 updated
        $this->assertEquals(10, $updated['stocks'][0]['quantity']);

        // now there should be 2 stocks (stock2 removed, new one added)
        $this->assertCount(2, $updated['stocks']);

        // old stock2 image should be deleted
        $this->assertFalse(Storage::disk('public')->exists($oldStock2Image));

        // new stock image exists
        $newStock = collect($updated['stocks'])->firstWhere('sku', 'UP-SKU-3');
        $this->assertNotNull($newStock);
        $this->assertTrue(Storage::disk('public')->exists($newStock['image_url']));
    }
}
