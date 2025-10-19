<?php

namespace Database\Seeders;

use App\Enums\PackagePriority;
use App\Enums\PackageStatus;
use App\Models\Package;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PackageSeeder extends Seeder
{
    /**
     * Método que carga paquetes de ejemplo en la base de datos.
     * @return void
     * @author paul quezada (paul.quezada[at]haulmer.com)
     * @version 2025-10-20
     */
    public function run(): void
    {
        $examples = [
            [
                'id' => 'PKG-0001',
                'priority' => PackagePriority::HIGH,
                'status' => PackageStatus::PENDING,
                'imported_at' => Carbon::now()->subDays(2),
            ],
            [
                'id' => 'PKG-0002',
                'priority' => PackagePriority::MEDIUM,
                'status' => PackageStatus::IN_TRANSIT,
                'imported_at' => Carbon::now()->subDay(),
            ],
            [
                'id' => 'PKG-0003',
                'priority' => PackagePriority::LOW,
                'status' => PackageStatus::DELIVERED,
                'imported_at' => Carbon::now()->subHours(12),
            ],
        ];

        foreach ($examples as $package) {
            Package::query()->updateOrCreate(
                ['id' => $package['id']],
                [
                    'priority' => $package['priority'],
                    'status' => $package['status'],
                    'imported_at' => $package['imported_at'],
                ]
            );
        }
    }
}
