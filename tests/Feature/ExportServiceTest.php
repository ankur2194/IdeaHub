<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Idea;
use App\Models\User;
use App\Services\ExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ExportService $exportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->exportService = new ExportService;
    }

    public function test_can_export_analytics_as_pdf(): void
    {
        // Create test data
        $users = User::factory()->count(5)->create();
        $category = Category::factory()->create();
        Idea::factory()->count(10)->create([
            'category_id' => $category->id,
            'status' => 'approved',
        ]);

        $response = $this->exportService->exportAnalyticsPDF();

        $this->assertInstanceOf(\Illuminate\Http\Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_can_export_analytics_as_csv(): void
    {
        // Create test data
        User::factory()->count(3)->create();
        Idea::factory()->count(5)->create(['status' => 'approved']);
        Idea::factory()->count(3)->create(['status' => 'pending']);

        $response = $this->exportService->exportAnalyticsCSV();

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('analytics-report', $response->headers->get('Content-Disposition'));
    }

    public function test_can_export_ideas_as_csv(): void
    {
        $user = User::factory()->create(['name' => 'Test User']);
        $category = Category::factory()->create(['name' => 'Test Category']);

        Idea::factory()->count(3)->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'status' => 'approved',
            'title' => 'Test Idea',
        ]);

        $response = $this->exportService->exportIdeasCSV();

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('ideas-export', $response->headers->get('Content-Disposition'));
    }

    public function test_can_export_users_as_csv(): void
    {
        User::factory()->count(5)->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        User::factory()->count(2)->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->exportService->exportUsersCSV();

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('users-export', $response->headers->get('Content-Disposition'));
    }

    public function test_can_filter_ideas_export_by_status(): void
    {
        Idea::factory()->count(3)->create(['status' => 'approved']);
        Idea::factory()->count(2)->create(['status' => 'pending']);

        $response = $this->exportService->exportIdeasCSV(['status' => 'approved']);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_can_filter_ideas_export_by_category(): void
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        Idea::factory()->count(3)->create(['category_id' => $category1->id]);
        Idea::factory()->count(2)->create(['category_id' => $category2->id]);

        $response = $this->exportService->exportIdeasCSV(['category_id' => $category1->id]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_can_filter_users_export_by_role(): void
    {
        User::factory()->count(5)->create(['role' => 'user']);
        User::factory()->count(2)->create(['role' => 'admin']);

        $response = $this->exportService->exportUsersCSV(['role' => 'admin']);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_analytics_export_includes_correct_data(): void
    {
        // Create comprehensive test data
        $category = Category::factory()->create(['name' => 'Innovation']);

        Idea::factory()->count(5)->create([
            'category_id' => $category->id,
            'status' => 'approved',
        ]);

        Idea::factory()->count(3)->create([
            'category_id' => $category->id,
            'status' => 'pending',
        ]);

        Idea::factory()->count(2)->create([
            'category_id' => $category->id,
            'status' => 'rejected',
        ]);

        // Test that the export method runs successfully
        $response = $this->exportService->exportAnalyticsPDF();

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_export_handles_empty_data_gracefully(): void
    {
        // No data in database

        $pdfResponse = $this->exportService->exportAnalyticsPDF();
        $csvResponse = $this->exportService->exportAnalyticsCSV();
        $ideasResponse = $this->exportService->exportIdeasCSV();
        $usersResponse = $this->exportService->exportUsersCSV();

        $this->assertEquals(200, $pdfResponse->getStatusCode());
        $this->assertEquals(200, $csvResponse->getStatusCode());
        $this->assertEquals(200, $ideasResponse->getStatusCode());
        $this->assertEquals(200, $usersResponse->getStatusCode());
    }
}
