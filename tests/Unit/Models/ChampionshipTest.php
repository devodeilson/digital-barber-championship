<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Championship;
use App\Models\User;
use App\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class ChampionshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_championship()
    {
        $championship = Championship::factory()->create();
        
        $this->assertDatabaseHas('championships', [
            'id' => $championship->id,
            'status' => 'draft'
        ]);
    }

    public function test_can_activate_championship()
    {
        $championship = Championship::factory()->create();
        
        $championship->activate();
        
        $this->assertEquals('active', $championship->status);
    }

    public function test_cannot_activate_non_draft_championship()
    {
        $championship = Championship::factory()->create(['status' => 'active']);
        
        $this->expectException(\Exception::class);
        
        $championship->activate();
    }

    public function test_can_join_championship()
    {
        $championship = Championship::factory()->create(['status' => 'active']);
        $user = User::factory()->create(['rating' => 5]);
        
        $this->assertTrue($championship->canJoin($user));
    }

    public function test_cannot_join_full_championship()
    {
        $championship = Championship::factory()->create([
            'status' => 'active',
            'max_participants' => 1
        ]);
        
        $existingUser = User::factory()->create();
        $championship->participants()->attach($existingUser);
        
        $newUser = User::factory()->create();
        
        $this->assertFalse($championship->canJoin($newUser));
    }

    public function test_calculate_winners()
    {
        $championship = Championship::factory()->create(['status' => 'active']);
        
        $contents = Content::factory()->count(5)->create([
            'championship_id' => $championship->id,
            'status' => 'approved'
        ]);
        
        // Simular votos
        foreach ($contents as $index => $content) {
            $content->update(['average_rating' => 5 - $index]);
        }
        
        $winners = $championship->calculateWinners();
        
        $this->assertEquals(3, $winners->count());
        $this->assertEquals(5, $winners->first()->average_rating);
    }

    public function test_delete_championship_removes_banner()
    {
        Storage::fake('public');
        
        $championship = Championship::factory()->create([
            'banner' => 'banners/test.jpg'
        ]);
        
        Storage::disk('public')->put('banners/test.jpg', 'test');
        
        $championship->delete();
        
        Storage::disk('public')->assertMissing('banners/test.jpg');
    }
} 