<?php

namespace Tests\Performance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Championship;
use App\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

class ApiPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Criar dados de teste em massa
        User::factory()->count(100)->create();
        Championship::factory()->count(20)->create();
        Content::factory()->count(500)->create();
    }

    public function test_championships_list_performance()
    {
        Sanctum::actingAs(User::first());

        $startTime = microtime(true);
        
        $response = $this->getJson('/api/championships');
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // em milissegundos

        $response->assertStatus(200);
        $this->assertLessThan(500, $executionTime, 'Lista de campeonatos deve carregar em menos de 500ms');
        
        // Verificar número de queries
        $queryCount = count(DB::getQueryLog());
        $this->assertLessThan(10, $queryCount, 'Não deve executar mais que 10 queries');
    }

    public function test_content_list_performance()
    {
        Sanctum::actingAs(User::first());

        $startTime = microtime(true);
        
        $response = $this->getJson('/api/contents');
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(1000, $executionTime, 'Lista de conteúdos deve carregar em menos de 1 segundo');
        
        $queryCount = count(DB::getQueryLog());
        $this->assertLessThan(15, $queryCount, 'Não deve executar mais que 15 queries');
    }

    public function test_concurrent_requests_performance()
    {
        $user = User::first();
        Sanctum::actingAs($user);

        $startTime = microtime(true);
        
        $promises = [];
        for ($i = 0; $i < 10; $i++) {
            $promises[] = $this->getJson('/api/championships');
        }

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(2000, $executionTime, '10 requisições concorrentes devem completar em menos de 2 segundos');
    }

    public function test_memory_usage()
    {
        $startMemory = memory_get_usage();
        
        Sanctum::actingAs(User::first());
        $response = $this->getJson('/api/championships');
        
        $endMemory = memory_get_usage();
        $memoryUsed = ($endMemory - $startMemory) / 1024 / 1024; // em MB

        $this->assertLessThan(50, $memoryUsed, 'Não deve usar mais que 50MB de memória');
    }
} 