<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class OpenAIControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testSendMessage()
    {
        // Create a mock authenticated user (you may need to adjust this based on your auth system)
        $user = User::factory()->create();

        // Simulate authentication
        $this->actingAs($user);

        // Send a POST request to the sendMessage endpoint
        $response = $this->postJson('/send-message', [
            'message' => 'Test message',
            'personality' => 'friendly', // Adjust as needed for different personalities
        ]);

        // Assert the response is successful (status code 200)
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Message sent successfully',
            ]);

        // Assert the message is saved in the database
        $this->assertDatabaseHas('messages', [
            'user_id' => $user->id,
            'user_message' => 'Test message',
        ]);
    }
}