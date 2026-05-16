<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_view_published_posts()
    {
        $user = User::factory()->create();
        $category = BlogCategory::create(['name_pt' => 'Tech', 'name_en' => 'Tech', 'slug' => 'tech']);
        
        $post = BlogPost::create([
            'title' => 'My Post',
            'slug' => 'my-post',
            'content' => 'Content',
            'category_id' => $category->id,
            'author_id' => $user->id,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->getJson('/api/public/blog/posts');
        $response->assertStatus(200);
        $response->assertJsonPath('data.0.slug', 'my-post');
    }

    public function test_admin_can_create_post()
    {
        \Spatie\Permission\Models\Role::create(['name' => 'admin']);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->postJson('/api/admin/blog/posts', [
            'title' => 'New Post',
            'slug' => 'new-post',
            'content' => 'Hello',
            'status' => 'draft',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('blog_posts', ['slug' => 'new-post']);
    }
}
