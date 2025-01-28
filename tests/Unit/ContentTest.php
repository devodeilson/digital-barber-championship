<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Content;
use App\Models\User;
use App\Models\Championship;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_content()
    {
        $user = User::factory()->create();
        $championship = Championship::factory()->create();
        
        $content = Content::factory()->create([
            'user_id' => $user->id,
            'championship_id' => $championship->id
        ]);
        
        $this->assertDatabaseHas('contents', [
            'id' => $content->id,
            'user_id' => $user->id,
            'championship_id' => $championship->id
        ]);
    }

    public function test_can_approve_content()
    {
        $content = Content::factory()->create(['status' => 'pending']);
        
        $content->approve();
        
        $this->assertEquals('approved', $content->status);
    }

    public function test_can_reject_content()
    {
        $content = Content::factory()->create(['status' => 'pending']);
        
        $content->reject();
        
        $this->assertEquals('rejected', $content->status);
    }

    public function test_can_calculate_average_rating()
    {
        $content = Content::factory()->create();
        
        $content->votes()->createMany([
            ['rating' => 4, 'user_id' => User::factory()->create()->id],
            ['rating' => 5, 'user_id' => User::factory()->create()->id],
            ['rating' => 3, 'user_id' => User::factory()->create()->id]
        ]);

        $content->updateAverageRating();

        $this->assertEquals(4.0, $content->average_rating);
    }

    public function test_content_belongs_to_user()
    {
        $user = User::factory()->create();
        $content = Content::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($content->user->is($user));
    }

    public function test_content_belongs_to_championship()
    {
        $championship = Championship::factory()->create();
        $content = Content::factory()->create(['championship_id' => $championship->id]);

        $this->assertTrue($content->championship->is($championship));
    }
} 