<?php

namespace Tests\Feature;

use App\Models\ChatMessage;
use App\Models\Order;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ChatScrollTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create role
        Role::create(['name' => 'SILVERCHANNEL']);
    }

    /**
     * Test that messages are returned in the correct order for the initial load.
     * The controller returns paginated data (descending) but reverses the collection
     * so the UI receives them Oldest -> Newest.
     */
    public function test_messages_are_returned_in_correct_order()
    {
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'phone' => '08123456789',
            'nik' => '1234567890123456',
            'address' => 'Jl. Test No. 1',
            'province_id' => 1,
            'city_id' => 1,
            'subdistrict_id' => 1,
            'postal_code' => '12345',
            'birth_place' => 'Jakarta',
            'birth_date' => '1990-01-01',
            'gender' => 'L',
            'religion' => 'Islam',
            'marital_status' => 'Single',
            'job' => 'Developer',
            'status' => 'ACTIVE',
        ]);
        $user->assignRole('SILVERCHANNEL');
        
        $order = Order::factory()->create(['user_id' => $user->id]);

        // Create 15 messages with specific timestamps
        // Sequence ensures created_at increases
        $messages = ChatMessage::factory()->count(15)->sequence(fn ($sequence) => [
            'created_at' => now()->subMinutes(15 - $sequence->index),
        ])->create([
            'order_id' => $order->id,
            'sender_id' => $user->id,
        ]);

        $this->actingAs($user);

        $response = $this->getJson(route('silverchannel.orders.messages', $order));

        $response->assertStatus(200);
        
        // Assert paginated structure
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'message', 'created_at']
            ],
            'current_page',
            'next_page_url'
        ]);

        // Check that we got all messages and they are ordered by created_at ASC (Oldest First)
        // because the controller reverses the paginated DESC result.
        $data = $response->json('data');
        $this->assertCount(15, $data);
        
        $firstMessage = $messages->sortBy('created_at')->first();
        $lastMessage = $messages->sortBy('created_at')->last();

        // The API returns array_reverse of the DESC pagination, so index 0 is oldest
        $this->assertEquals($firstMessage->id, $data[0]['id']);
        $this->assertEquals($lastMessage->id, $data[14]['id']);
    }

    /**
     * Test polling for new messages (after_id).
     * This validates the logic used for the "New Message" indicator when scrolled up.
     */
    public function test_polling_returns_only_new_messages()
    {
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::factory()->create([
            'email_verified_at' => now(),
            'phone' => '08123456789',
            'nik' => '1234567890123456',
            'address' => 'Jl. Test No. 1',
            'province_id' => 1,
            'city_id' => 1,
            'subdistrict_id' => 1,
            'postal_code' => '12345',
            'birth_place' => 'Jakarta',
            'birth_date' => '1990-01-01',
            'gender' => 'L',
            'religion' => 'Islam',
            'marital_status' => 'Single',
            'job' => 'Developer',
            'status' => 'ACTIVE',
        ]);
        $user->assignRole('SILVERCHANNEL');
        
        $order = Order::factory()->create(['user_id' => $user->id]);

        // Create 10 initial messages
        $oldMessages = ChatMessage::factory()->count(10)->sequence(fn ($sequence) => [
            'created_at' => now()->subMinutes(10 - $sequence->index),
        ])->create([
            'order_id' => $order->id,
            'sender_id' => $user->id,
        ]);

        $lastOldMessageId = $oldMessages->last()->id;

        // Create 5 new messages (simulating incoming messages while user is on page)
        $newMessages = ChatMessage::factory()->count(5)->sequence(fn ($sequence) => [
            'created_at' => now()->addMinutes($sequence->index),
        ])->create([
            'order_id' => $order->id,
            'sender_id' => $user->id,
        ]);

        $this->actingAs($user);

        // Poll with after_id
        $response = $this->getJson(route('silverchannel.orders.messages', [
            'order' => $order,
            'after_id' => $lastOldMessageId
        ]));

        $response->assertStatus(200);
        
        // Assert it returns 'data' wrapper with new messages only
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'message', 'created_at']
            ]
        ]);

        $data = $response->json('data');
        $this->assertCount(5, $data);

        $firstNewMessage = $newMessages->first();
        $lastNewMessage = $newMessages->last();

        $this->assertEquals($firstNewMessage->id, $data[0]['id']);
        $this->assertEquals($lastNewMessage->id, $data[4]['id']);
    }
}
