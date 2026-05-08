<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Favorite;

class FavoriteTest extends TestCase
{
    use RefreshDatabase; 
    public function test_can_save_city_to_favorites()
    {
        $response = $this->postJson('/favorites', [
            'city_name' => 'Cairo',
            'country_code' => 'EG',
            'notes' => 'Beautiful city'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['status' => 'success']);
        $this->assertDatabaseHas('favorites', [
            'city_name' => 'Cairo'
        ]);
    }
    public function test_can_get_favorites_list()
    {
        Favorite::forceCreate([
            'city_name' => 'Dubai',
            'country_code' => 'AE',
            'notes' => 'Nice weather'
        ]);
        $response = $this->getJson('/favorites');
        $response->assertStatus(200)
                 ->assertJson(['status' => 'success']);
    }

    public function test_can_delete_a_favorite()
    {
        $favorite = Favorite::forceCreate([
            'city_name' => 'Tokyo',
            'country_code' => 'JP',
            'notes' => 'Sunny'
        ]);
        $response = $this->deleteJson('/favorites/' . $favorite->id);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('favorites', [
            'id' => $favorite->id
        ]);
    }
}