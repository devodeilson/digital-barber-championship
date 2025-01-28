<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Championship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChampionshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_championship()
    {
        $championship = Championship::factory()->create();
        
        $this->assertDatabaseHas('championships', [
            'id' => $championship->id,
            'name' => $championship->name
        ]);
    }

    public function test_can_add_participant()
    {
        $championship = Championship::factory()->create();
        $user = User::factory()->create();

        $championship->participants()->attach($user->id);

        $this->assertTrue($championship->hasParticipant($user->id));
    }

    public function test_can_activate_championship()
    {
        $championship = Championship::factory()->create(['status' => 'draft']);
        
        $championship->activate();
        
        $this->assertEquals('active', $championship->status);
    }

    public function test_can_finish_championship()
    {
        $championship = Championship::factory()->create(['status' => 'active']);
        
        $championship->finish();
        
        $this->assertEquals('finished', $championship->status);
    }

    public function test_cannot_join_finished_championship()
    {
        $championship = Championship::factory()->create(['status' => 'finished']);
        $user = User::factory()->create();

        $this->assertFalse($championship->canJoin($user));
    }

    public function test_cannot_exceed_max_participants()
    {
        $championship = Championship::factory()->create([
            'max_participants' => 2,
            'status' => 'active'
        ]);

        $users = User::factory()->count(3)->create();

        $championship->participants()->attach($users[0]->id);
        $championship->participants()->attach($users[1]->id);

        $this->assertFalse($championship->canJoin($users[2]));
    }
} 