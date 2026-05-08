<?php

namespace Tests\Unit;

use Tests\TestCase; 
use Illuminate\Foundation\Testing\RefreshDatabase;

class FavoriteValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_city_name_is_required()
    {
        $response = $this->postJson('/favorites', [
            'city_name' => '', 
            'country_code' => 'US',
            'notes' => 'Test notes'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('city_name');
    }
    public function test_city_name_cannot_contain_numbers()
    {
        $response = $this->postJson('/favorites', [
            'city_name' => 'Cairo123', 
            'country_code' => 'EG',
            'notes' => 'Test notes'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('city_name');
    }
}