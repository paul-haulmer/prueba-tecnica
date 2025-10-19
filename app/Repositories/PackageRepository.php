<?php

namespace App\Repositories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PackageRepository
{
    /**
     * Método que persiste un lote de paquetes en la base de datos.
     * @param array<int, array<string, mixed>> $packages
     * @return void
     * @author paul quezada (paul.quezada[at]haulmer.com)
     * @version 2025-10-20
     */
    public function insertMany(array $packages): void
    {
        DB::transaction(function () use ($packages): void {
            $now = Carbon::now();
            $payload = array_map(
                static function (array $package) use ($now): array {
                    return [
                        'id' => $package['id'],
                        'priority' => $package['priority'],
                        'status' => $package['status'],
                        'imported_at' => $package['imported_at'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                },
                $packages
            );

            Package::insert($payload);
        });
    }

    /**
     * Método que recupera paquetes a partir de sus identificadores.
     * @param array<int, string> $ids
     * @return Collection<int, Package>
     * @author paul quezada (paul.quezada[at]haulmer.com)
     * @version 2025-10-20
     */
    public function findByIds(array $ids): Collection
    {
        if ($ids === []) {
            return new Collection();
        }

        return Package::whereIn('id', $ids)->get();
    }

    /**
     * Método que obtiene un paquete por su identificador.
     * @param string $id
     * @return Package|null
     * @author paul quezada (paul.quezada[at]haulmer.com)
     * @version 2025-10-20
     */
    public function find(string $id): ?Package
    {
        return Package::find($id);
    }

    /**
     * Método que lista paquetes filtrando opcionalmente por estado y prioridad.
     * @param string|null $status
     * @param string|null $priority
     * @return Collection<int, Package>
     * @author paul quezada (paul.quezada[at]haulmer.com)
     * @version 2025-10-20
     */
    public function list(?string $status = null, ?string $priority = null): Collection
    {
        return Package::when($status, static fn ($query) => $query->where('status', $status))
            ->when($priority, static fn ($query) => $query->where('priority', $priority))
            ->orderByDesc('imported_at')
            ->get();
    }

    /**
     * Método que guarda los cambios de un paquete.
     * @param Package $package
     * @return bool
     * @author paul quezada (paul.quezada[at]haulmer.com)
     * @version 2025-10-20
     */
    public function save(Package $package): bool
    {
        return $package->save();
    }

    /**
     * Método que elimina un paquete de la base de datos.
     * @param Package $package
     * @return bool
     * @author paul quezada (paul.quezada[at]haulmer.com)
     * @version 2025-10-20
     */
    public function delete(Package $package): bool
    {
        return $package->delete();
    }

    /**
     * Método que obtiene los identificadores existentes para evitar duplicados.
     * @param array<int, string> $ids
     * @return array<int, string>
     * @author paul quezada (paul.quezada[at]haulmer.com)
     * @version 2025-10-20
     */
    public function findExistingIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return Package::whereIn('id', $ids)->pluck('id')->all();
    }
}
