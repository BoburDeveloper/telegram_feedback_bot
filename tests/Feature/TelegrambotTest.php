<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TelegrambotTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->post('/form/messaging/4', [
            'name' => 'Ali',
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors(['name', 'email']);
        $response->assertStatus(200);
    }
}
