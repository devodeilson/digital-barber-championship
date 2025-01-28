<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Content;
use App\Models\Vote;
use App\Models\Championship;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VotingSystemTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $championship;
    protected $content;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->championship = Championship::factory()->create(['status' => 'active']);
        $this->content = Content::factory()->create([
            'championship_id' => $this->championship->id,
            'status' => 'approved'
        ]);
    }

    public function test_user_can_vote_on_content()
    {
        $response = $this->actingAs($this->user)
            ->post(route('votes.store'), [
                'content_id' => $this->content->id,
                'rating' => 5,
                'comment' => 'Great content!'
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('votes', [
            'user_id' => $this->user->id,
            'content_id' => $this->content->id,
            'rating' => 5
        ]);
    }

    public function test_user_cannot_vote_twice_on_same_content()
    {
        Vote::factory()->create([
            'user_id' => $this->user->id,
            'content_id' => $this->content->id
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('votes.store'), [
                'content_id' => $this->content->id,
                'rating' => 4
            ]);

        $response->assertStatus(422);
    }

    public function test_user_can_update_vote_within_24_hours()
    {
        $vote = Vote::factory()->create([
            'user_id' => $this->user->id,
            'content_id' => $this->content->id,
            'created_at' => now()->subHours(23)
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('votes.update', $vote), [
                'rating' => 4,
                'comment' => 'Updated comment'
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals(4, $vote->fresh()->rating);
    }

    public function test_user_cannot_update_vote_after_24_hours()
    {
        $vote = Vote::factory()->create([
            'user_id' => $this->user->id,
            'content_id' => $this->content->id,
            'created_at' => now()->subHours(25)
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('votes.update', $vote), [
                'rating' => 4
            ]);

        $response->assertStatus(403);
    }

    public function test_content_rating_is_updated_after_vote()
    {
        Vote::factory()->count(3)->create([
            'content_id' => $this->content->id,
            'rating' => 5
        ]);

        $this->actingAs($this->user)
            ->post(route('votes.store'), [
                'content_id' => $this->content->id,
                'rating' => 4
            ]);

        $this->content->refresh();
        
        $this->assertEquals(4.75, $this->content->average_rating);
        $this->assertEquals(4, $this->content->total_votes);
    }
} 