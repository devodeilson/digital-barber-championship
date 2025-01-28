<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Championship;
use App\Jobs\GenerateChampionshipReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Queue;

class ReportGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $championship;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->championship = Championship::factory()->create();
    }

    public function test_admin_can_request_report_generation()
    {
        Queue::fake();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.championships.generate-report', $this->championship));

        $response->assertRedirect()
            ->assertSessionHas('success');

        Queue::assertPushed(GenerateChampionshipReport::class);
    }

    public function test_report_is_generated_with_correct_data()
    {
        Storage::fake('local');

        $participants = User::factory()->count(3)->create();
        $this->championship->participants()->attach($participants);

        $job = new GenerateChampionshipReport($this->championship);
        $job->handle();

        $this->assertNotNull($this->championship->fresh()->report_url);
        Storage::disk('local')->assertExists($this->championship->report_url);
    }

    public function test_report_includes_all_required_information()
    {
        Storage::fake('local');

        $participants = User::factory()->count(3)->create();
        $this->championship->participants()->attach($participants);

        foreach ($participants as $participant) {
            $contents = Content::factory()->count(2)->create([
                'user_id' => $participant->id,
                'championship_id' => $this->championship->id,
                'average_rating' => 4.5
            ]);
        }

        $job = new GenerateChampionshipReport($this->championship);
        $job->handle();

        $reportPath = Storage::path($this->championship->fresh()->report_url);
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($reportPath);
        $worksheet = $spreadsheet->getActiveSheet();

        $this->assertEquals('Participante', $worksheet->getCell('A1')->getValue());
        $this->assertEquals('Email', $worksheet->getCell('B1')->getValue());
        $this->assertEquals(3, $worksheet->getHighestRow());
    }

    public function test_old_report_is_deleted_when_new_one_is_generated()
    {
        Storage::fake('local');

        $oldReportUrl = 'reports/old_report.xlsx';
        $this->championship->update(['report_url' => $oldReportUrl]);
        Storage::put($oldReportUrl, 'old content');

        $job = new GenerateChampionshipReport($this->championship);
        $job->handle();

        Storage::assertMissing($oldReportUrl);
        $this->assertNotEquals($oldReportUrl, $this->championship->fresh()->report_url);
    }
} 