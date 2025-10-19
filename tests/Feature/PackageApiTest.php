<?php

namespace Tests\Feature;

use App\Enums\PackagePriority;
use App\Enums\PackageStatus;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PackageApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_requests_without_api_key_are_rejected(): void
    {
        $this->getJson('/api/packages')->assertStatus(401);
    }

    public function test_it_lists_packages_with_filters(): void
    {
        Package::factory()->create([
            'priority' => PackagePriority::HIGH,
            'status' => PackageStatus::PENDING,
        ]);

        Package::factory()->create([
            'priority' => PackagePriority::LOW,
            'status' => PackageStatus::DELIVERED,
        ]);

        $response = $this->getJson('/api/packages?status=pending', $this->headers());

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', PackageStatus::PENDING->value);
    }

    public function test_it_imports_packages_successfully(): void
    {
        $payload = [
            'packages' => [
                [
                    'id' => 'PKG-401',
                    'priority' => PackagePriority::HIGH->value,
                    'status' => PackageStatus::PENDING->value,
                    'imported_at' => Carbon::now()->toISOString(),
                ],
            ],
        ];

        $response = $this->postJson('/api/packages/import', $payload, $this->headers());

        $response->assertCreated()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', 'PKG-401');

        $this->assertDatabaseHas('packages', ['id' => 'PKG-401']);
    }

    public function test_it_updates_and_deletes_packages(): void
    {
        $package = Package::factory()->create([
            'status' => PackageStatus::PENDING,
        ]);

        $this->patchJson(
            "/api/packages/{$package->id}/status",
            ['status' => PackageStatus::DELIVERED->value],
            $this->headers()
        )->assertOk()
            ->assertJsonPath('data.status', PackageStatus::DELIVERED->value);

        $this->deleteJson("/api/packages/{$package->id}", [], $this->headers())
            ->assertStatus(400);

        $pending = Package::factory()->create([
            'status' => PackageStatus::PENDING,
        ]);

        $this->deleteJson("/api/packages/{$pending->id}", [], $this->headers())
            ->assertNoContent();

        $this->assertDatabaseMissing('packages', ['id' => $pending->id]);
    }

    private function headers(): array
    {
        return [
            'X-API-Key' => config('services.packages.api_key'),
        ];
    }
}
