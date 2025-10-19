<?php

namespace Database\Factories;

use App\Enums\PackagePriority;
use App\Enums\PackageStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Package>
 */
class PackageFactory extends Factory
{
    /**
     * Método que define el estado por defecto del modelo Package.
     * @return array<string, mixed>
     * @author paul quezada (paul.quezada[at]haulmer.com)
     * @version 2025-10-20
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'priority' => $this->faker->randomElement(PackagePriority::values()),
            'status' => $this->faker->randomElement(PackageStatus::values()),
            'imported_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
