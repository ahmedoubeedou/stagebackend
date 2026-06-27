<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CarMarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_login()
    {
        // 1. Test Registration
        $registerResponse = $this->postJson('/api/register', [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'password123',
            'phone'                 => '+222 1234 5678',
        ]);

        $registerResponse->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'email', 'phone']
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'phone' => '+222 1234 5678'
        ]);

        // 2. Test Login
        $loginResponse = $this->postJson('/api/login', [
            'email'    => 'john@example.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'token',
                    'user'
                ]
            ]);

        $token = $loginResponse->json('data.token');

        // 3. Test Get Profile (Me)
        $meResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/user');

        $meResponse->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'email' => 'john@example.com'
                ]
            ]);
    }

    public function test_car_listings_crud_and_favorites()
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'phone' => '+222 9999 8888'
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        // 1. Test Store Car
        $imageFile = UploadedFile::fake()->image('car1.jpg');
        $videoFile = UploadedFile::fake()->create('video.mp4', 500, 'video/mp4');

        $storeResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/cars', [
                'brand'        => 'Toyota',
                'model'        => 'Corolla',
                'year'         => 2022,
                'price'        => 15000.00,
                'mileage'      => 30000,
                'fuel_type'    => 'gasoline',
                'transmission' => 'automatic',
                'color'        => 'Blue',
                'location'     => 'Nouakchott',
                'description'  => 'Excellent condition.',
                'images'       => [$imageFile],
                'video'        => $videoFile
            ]);

        $storeResponse->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'brand',
                    'model',
                    'images',
                    'video',
                    'seller' => ['name', 'phone']
                ]
            ]);

        $carId = $storeResponse->json('data.id');
        $this->assertEquals('Toyota', $storeResponse->json('data.brand'));
        $this->assertCount(1, $storeResponse->json('data.images'));
        $this->assertNotNull($storeResponse->json('data.video'));

        // 2. Test Get Public Cars List
        $listResponse = $this->getJson('/api/cars');
        $listResponse->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'data',
                    'current_page',
                    'total'
                ]
            ]);
        
        $this->assertCount(1, $listResponse->json('data.data'));

        // 3. Test Show Single Car
        $showResponse = $this->getJson('/api/cars/' . $carId);
        $showResponse->assertStatus(200)
            ->assertJsonPath('data.brand', 'Toyota');

        // 4. Test Favorites
        // Check if not favorited
        $isFavResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/user/favorites/' . $carId);
        $isFavResponse->assertStatus(200)->assertJsonPath('data', false);

        // Add to favorites
        $addFavResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/user/favorites/' . $carId);
        $addFavResponse->assertStatus(200)->assertJsonPath('status', 'success');

        // Check if favorited now
        $isFavResponse2 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/user/favorites/' . $carId);
        $isFavResponse2->assertStatus(200)->assertJsonPath('data', true);

        // Get favorites list
        $favListResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/user/favorites');
        $favListResponse->assertStatus(200);
        $this->assertCount(1, $favListResponse->json('data'));

        // Remove from favorites
        $removeFavResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/user/favorites/' . $carId);
        $removeFavResponse->assertStatus(200);

        // Check again
        $isFavResponse3 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/user/favorites/' . $carId);
        $isFavResponse3->assertStatus(200)->assertJsonPath('data', false);

        // 5. Test Update Car Listing
        $updateResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/cars/' . $carId, [
                'brand'        => 'Toyota',
                'model'        => 'Corolla SE',
                'year'         => 2023,
                'price'        => 16000.00,
                'mileage'      => 35000,
                'fuel_type'    => 'hybrid',
                'transmission' => 'automatic',
                'images'       => [$storeResponse->json('data.images.0')], // keep old image
                'video'        => 'delete', // delete video
            ], [
                'X-HTTP-Method-Override' => 'PUT'
            ]);

        $updateResponse->assertStatus(200);
        $this->assertEquals('Corolla SE', $updateResponse->json('data.model'));
        $this->assertEquals('hybrid', $updateResponse->json('data.fuel_type'));
        $this->assertNull($updateResponse->json('data.video')); // video deleted

        // 6. Test Delete Car Listing
        $deleteResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/cars/' . $carId);

        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('cars', ['id' => $carId]);
    }
}
