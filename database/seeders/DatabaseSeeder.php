<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Método que ejecuta los seeders necesarios para la aplicación.
     * @return void
     * @author paul quezada (paul.quezada[at]haulmer.com)
     * @version 2025-10-20
     */
    public function run(): void
    {
        $this->call([
            PackageSeeder::class,
        ]);
    }
}
