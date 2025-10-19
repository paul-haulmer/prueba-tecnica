<?php

namespace App\Services;

use App\Enums\PackagePriority;
use App\Enums\PackageStatus;
use App\Exceptions\DuplicatePackageException;
use App\Exceptions\PackageDeletionNotAllowedException;
use App\Models\Package;
use App\Repositories\PackageRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class PackageService
{
    public function __construct(
        private readonly PackageRepository $repository
    ) {
    }

    /**
     * Método que importa paquetes nuevos validando duplicados.
     * @param array<int, array<string, mixed>> $payload
     * @return Collection<int, Package>
     * @throws DuplicatePackageException
     * @author paul quezada (paul.quezada[at]haulmer.com)
     * @version 2025-10-20
     */
    public function import(array $payload): Collection
    {
        if ($payload === []) {
            return new Collection();
        }

        $ids = array_map(static fn (array $package): string => $package['id'], $payload);

        $duplicatesInRequest = $this->findDuplicates($ids);
        if ($duplicatesInRequest !== []) {
            throw new DuplicatePackageException($duplicatesInRequest, 'Payload includes duplicate package IDs.');
        }

        $existing = $this->repository->findExistingIds($ids);
        if ($existing !== []) {
            throw new DuplicatePackageException($existing, 'Packages already exist with these IDs.');
        }

        $normalized = array_map(
            static function (array $package): array {
                return [
                    'id' => $package['id'],
                    'priority' => $package['priority'],
                    'status' => $package['status'],
                    'imported_at' => Carbon::parse($package['imported_at']),
                ];
            },
            $payload
        );

        $this->repository->insertMany($normalized);

        $packages = $this->repository->findByIds($ids);

        return $packages->sortBy(static function (Package $package) use ($ids): int {
            $position = array_search($package->id, $ids, true);

            return $position === false ? PHP_INT_MAX : $position;
        })->values();
    }

    /**
     * Método que lista los paquetes aplicando filtros opcionales.
     * @param PackageStatus|null $status
     * @param PackagePriority|null $priority
     * @return Collection<int, Package>
     * @author paul quezada (paul.quezada[at]haulmer.com)
     * @version 2025-10-20
     */
    public function list(?PackageStatus $status = null, ?PackagePriority $priority = null): Collection
    {
        return $this->repository->list($status?->value, $priority?->value);
    }

    /**
     * Método que actualiza el estado de un paquete.
     * @param string $id
     * @param PackageStatus $status
     * @return Package|null
     * @author paul quezada (paul.quezada[at]haulmer.com)
     * @version 2025-10-20
     */
    public function updateStatus(string $id, PackageStatus $status): ?Package
    {
        $package = $this->repository->find($id);

        if (! $package) {
            return null;
        }

        $package->status = $status;
        $this->repository->save($package);

        return $package;
    }

    /**
     * Método que elimina un paquete solo si está pendiente.
     * @param string $id
     * @return bool
     * @throws PackageDeletionNotAllowedException
     * @author paul quezada (paul.quezada[at]haulmer.com)
     * @version 2025-10-20
     */
    public function delete(string $id): bool
    {
        $package = $this->repository->find($id);

        if (! $package) {
            return false;
        }

        if ($package->status !== PackageStatus::PENDING) {
            throw new PackageDeletionNotAllowedException('Only pending packages can be removed.');
        }

        return $this->repository->delete($package);
    }

    /**
     * Método que identifica duplicados en un conjunto de identificadores.
     * @param array<int, string> $ids
     * @return array<int, string>
     * @author paul quezada (paul.quezada[at]haulmer.com)
     * @version 2025-10-20
     */
    private function findDuplicates(array $ids): array
    {
        $counts = array_count_values($ids);

        return array_keys(array_filter($counts, static fn (int $count): bool => $count > 1));
    }
}
