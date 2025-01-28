<?php

namespace Tests\Feature\Controllers\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Content;
use App\Models\Championship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ContentApproved;
use App\Notifications\ContentRejected;

class ContentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_view_content_list()
    {
        Content::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.contents.index'));

        $response->assertStatus(200)
            ->assertViewIs('admin.contents.index')
            ->assertViewHas('contents');
    }

    public function test_admin_can_filter_contents()
    {
        $championship = Championship::factory()->create(['name' => 'Test Championship']);
        $content = Content::factory()->create([
            'championship_id' => $championship->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.contents.index', [
                'search' => 'Test Championship',
                'status' => 'pending'
            ]));

        $response->assertStatus(200)
            ->assertViewHas('contents', function ($contents) use ($content) {
                return $contents->contains($content);
            });
    }

    public function test_admin_can_view_content_details()
    {
        $content = Content::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.contents.show', $content));

        $response->assertStatus(200)
            ->assertViewIs('admin.contents.show')
            ->assertViewHas('content');
    }

    public function test_admin_can_approve_content()
    {
        Notification::fake();

        $content = Content::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.contents.approve', $content));

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals('approved', $content->fresh()->status);
        
        Notification::assertSentTo(
            $content->user,
            ContentApproved::class
        );
    }

    public function test_admin_can_reject_content()
    {
        Notification::fake();

        $content = Content::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.contents.reject', $content));

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals('rejected', $content->fresh()->status);
        
        Notification::assertSentTo(
            $content->user,
            ContentRejected::class
        );
    }

    public function test_admin_cannot_approve_already_approved_content()
    {
        $content = Content::factory()->create(['status' => 'approved']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.contents.approve', $content));

        $response->assertRedirect()
            ->assertSessionHas('error');
    }
} 