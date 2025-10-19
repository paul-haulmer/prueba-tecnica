<?php

namespace Tests\Unit;

use App\Enums\PackagePriority;
use App\Enums\PackageStatus;
use App\Exceptions\DuplicatePackageException;
use App\Exceptions\PackageDeletionNotAllowedException;
use App\Models\Package;
use App\Services\PackageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PackageServiceTest extends TestCase
{
    use RefreshDatabase;

    private PackageService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(PackageService::class);
    }

    public function test_it_imports_packages_successfully(): void
    {
        $payload = [
            [
                'id' => 'PKG-1001',
                'priority' => PackagePriority::HIGH->value,
                'status' => PackageStatus::PENDING->value,
                'imported_at' => Carbon::now()->toISOString(),
            ],
            [
                'id' => 'PKG-1002',
                'priority' => PackagePriority::LOW->value,
                'status' => PackageStatus::IN_TRANSIT->value,
                'imported_at' => Carbon::now()->toISOString(),
            ],
        ];

        $imported = $this->service->import($payload);

        $this->assertCount(2, $imported);
        $this->assertDatabaseHas('packages', ['id' => 'PKG-1001']);
    }

    public function test_it_rejects_duplicate_ids_in_payload(): void
    {
        $this->expectException(DuplicatePackageException::class);

        $payload = [
            [
                'id' => 'PKG-2001',
                'priority' => PackagePriority::MEDIUM->value,
                'status' => PackageStatus::PENDING->value,
                'imported_at' => Carbon::now()->toISOString(),
            ],
            [
                'id' => 'PKG-2001',
                'priority' => PackagePriority::LOW->value,
                'status' => PackageStatus::PENDING->value,
                'imported_at' => Carbon::now()->toISOString(),
            ],
        ];

        $this->service->import($payload);
    }

    public function test_it_rejects_import_when_id_already_exists(): void
    {
        Package::factory()->create([
            'id' => 'PKG-3001',
            'priority' => PackagePriority::HIGH,
            'status' => PackageStatus::PENDING,
            'imported_at' => Carbon::now()->subDay(),
        ]);

        $this->expectException(DuplicatePackageException::class);

        $this->service->import([
            [
                'id' => 'PKG-3001',
                'priority' => PackagePriority::HIGH->value,
                'status' => PackageStatus::PENDING->value,
                'imported_at' => Carbon::now()->toISOString(),
            ],
        ]);
    }

    public function test_it_updates_status(): void
    {
        $package = Package::factory()->create([
            'status' => PackageStatus::PENDING,
        ]);

        $updated = $this->service->updateStatus($package->id, PackageStatus::DELIVERED);

        $this->assertNotNull($updated);
        $this->assertEquals(PackageStatus::DELIVERED, $updated->status);
        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => PackageStatus::DELIVERED->value,
        ]);
    }

    public function test_it_disallows_deleting_non_pending_packages(): void
    {
        $package = Package::factory()->create([
            'status' => PackageStatus::DELIVERED,
        ]);

        $this->expectException(PackageDeletionNotAllowedException::class);

        $this->service->delete($package->id);
    }

    public function test_it_deletes_pending_package(): void
    {
        $package = Package::factory()->create([
            'status' => PackageStatus::PENDING,
        ]);

        $result = $this->service->delete($package->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('packages', ['id' => $package->id]);
    }
}
