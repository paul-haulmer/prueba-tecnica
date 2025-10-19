<?php

namespace App\Http\Controllers;

use App\Enums\PackagePriority;
use App\Enums\PackageStatus;
use App\Exceptions\DuplicatePackageException;
use App\Exceptions\PackageDeletionNotAllowedException;
use App\Http\Requests\ImportPackagesRequest;
use App\Http\Requests\UpdatePackageStatusRequest;
use App\Models\Package;
use App\Services\PackageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;

class PackageController extends Controller
{
    public function __construct(
        private readonly PackageService $service
    ) {
    }

    /**
     * Método que importa paquetes desde sistemas externos a la plataforma.
     * @return JsonResponse
     * @author paul quezada (paul.quezada[at]haulmer.com)
     * @version 2025-10-20
     */
    public function import(ImportPackagesRequest $request): JsonResponse
    {
        try {
            $packages = $this->service->import($request->validated()['packages']);
        } catch (DuplicatePackageException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'duplicate_ids' => $exception->getDuplicateIds(),
            ], Response::HTTP_CONFLICT);
        }

        return response()->json([
            'data' => $packages->map(fn (Package $package): array => $this->transform($package))->values(),
        ], Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/packages",
     *     summary="Listar paquetes con filtros opcionales",
     *     tags={"Packages"},
     *     security={{"packages_apikey":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Filtrar por estado",
     *         @OA\Schema(type="string", enum={"pending","in_transit","delivered"})
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         required=false,
     *         description="Filtrar por prioridad",
     *         @OA\Schema(type="string", enum={"low","medium","high"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Listado de paquetes",
     *         @OA\JsonContent(ref="#/components/schemas/PackageCollectionResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="API Key inválida",
     *         @OA\JsonContent(ref="#/components/schemas/PackageErrorResponse")
     *     )
     * )
     *
     * Método que lista los paquetes disponibles aplicando filtros opcionales.
     * @return JsonResponse
     * @author paul quezada (paul.quezada[at]haulmer.com)
     * @version 2025-10-20
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'status' => ['nullable', 'string', Rule::in(PackageStatus::values())],
            'priority' => ['nullable', 'string', Rule::in(PackagePriority::values())],
        ]);

        $packages = $this->service->list(
            isset($filters['status']) ? PackageStatus::from($filters['status']) : null,
            isset($filters['priority']) ? PackagePriority::from($filters['priority']) : null
        );

        return response()->json([
            'data' => $packages->map(fn (Package $package): array => $this->transform($package))->values(),
        ]);
    }

    /**
     * Método que actualiza el estado de un paquete específico.
     * @return JsonResponse
     * @author paul quezada (paul.quezada[at]haulmer.com)
     * @version 2025-10-20
     */
    public function update(UpdatePackageStatusRequest $request, string $packageId): JsonResponse
    {
        $status = PackageStatus::from($request->validated()['status']);
        $package = $this->service->updateStatus($packageId, $status);

        if (! $package) {
            return response()->json([
                'message' => 'Package not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => $this->transform($package),
        ]);
    }

    /**
     * Método que elimina un paquete cuando se encuentra en estado pendiente.
     * @return JsonResponse
     * @author paul quezada (paul.quezada[at]haulmer.com)
     * @version 2025-10-20
     */
    public function destroy(string $packageId): JsonResponse
    {
        try {
            $deleted = $this->service->delete($packageId);
        } catch (PackageDeletionNotAllowedException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        if (! $deleted) {
            return response()->json([
                'message' => 'Package not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    private function transform(Package $package): array
    {
        return [
            'id' => $package->id,
            'priority' => $package->priority->value,
            'status' => $package->status->value,
            'imported_at' => $package->imported_at?->toIso8601String(),
            'created_at' => $package->created_at?->toIso8601String(),
            'updated_at' => $package->updated_at?->toIso8601String(),
        ];
    }
}
