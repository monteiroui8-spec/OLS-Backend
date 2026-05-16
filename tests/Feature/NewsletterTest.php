<?php

namespace Tests\Feature;

use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_subscribe()
    {
        $response = $this->postJson('/api/public/newsletter', [
            'email' => 'test@example.com'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'test@example.com']);
    }

    public function test_public_can_unsubscribe()
    {
        NewsletterSubscriber::create(['email' => 'test@example.com', 'subscribed_at' => now()]);
        
        $response = $this->get('/api/public/newsletter/unsubscribe?email=test@example.com');
        $response->assertStatus(200);

        $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'test@example.com']);
        $this->assertNotNull(NewsletterSubscriber::first()->unsubscribed_at);
    }

    public function test_admin_can_send_newsletter()
    {
        \Spatie\Permission\Models\Role::create(['name' => 'admin']);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->assignRole('admin');

        NewsletterSubscriber::create(['email' => 'test@example.com', 'subscribed_at' => now()]);

        $response = $this->actingAs($admin)->postJson('/api/admin/newsletter/send', [
            'subject' => 'Hello',
            'content' => 'World',
        ]);

        $response->assertStatus(200);
    }
}
