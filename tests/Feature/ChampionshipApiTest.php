<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Championship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class ChampionshipApiTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user = User::factory()->create();
    }

    public function test_can_list_championships()
    {
        Championship::factory()->count(3)->create();
        
        Sanctum::actingAs($this->user);
        
        $response = $this->getJson('/api/championships');
        
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_admin_can_create_championship()
    {
        Sanctum::actingAs($this->admin);
        
        $data = Championship::factory()->raw();
        
        $response = $this->postJson('/api/championships', $data);
        
        $response->assertStatus(201)
            ->assertJsonFragment(['name' => $data['name']]);
    }

    public function test_non_admin_cannot_create_championship()
    {
        Sanctum::actingAs($this->user);
        
        $data = Championship::factory()->raw();
        
        $response = $this->postJson('/api/championships', $data);
        
        $response->assertStatus(403);
    }

    public function test_can_join_championship()
    {
        $championship = Championship::factory()->create(['status' => 'active']);
        
        Sanctum::actingAs($this->user);
        
        $response = $this->postJson("/api/championships/{$championship->id}/join");
        
        $response->assertStatus(200);
        $this->assertTrue($championship->hasParticipant($this->user->id));
    }

    public function test_cannot_join_finished_championship()
    {
        $championship = Championship::factory()->create(['status' => 'finished']);
        
        Sanctum::actingAs($this->user);
        
        $response = $this->postJson("/api/championships/{$championship->id}/join");
        
        $response->assertStatus(400);
        $this->assertFalse($championship->hasParticipant($this->user->id));
    }
} 