<?php

namespace App\OpenApi\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="PackageImportItem",
 *     type="object",
 *     required={"id","priority","status","imported_at"},
 *     @OA\Property(property="id", type="string", example="PKG-0007"),
 *     @OA\Property(property="priority", type="string", enum={"low","medium","high"}, example="medium"),
 *     @OA\Property(property="status", type="string", enum={"pending","in_transit","delivered"}, example="pending"),
 *     @OA\Property(property="imported_at", type="string", format="date-time", example="2025-10-18T15:30:00Z")
 * )
 *
 * @OA\Schema(
 *     schema="PackageResource",
 *     required={"id","priority","status","imported_at"},
 *     type="object",
 *     @OA\Property(property="id", type="string", example="PKG-0007"),
 *     @OA\Property(
 *         property="priority",
 *         type="string",
 *         description="Prioridad del paquete",
 *         enum={"low","medium","high"},
 *         example="high"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Estado actual del paquete",
 *         enum={"pending","in_transit","delivered"},
 *         example="pending"
 *     ),
 *     @OA\Property(
 *         property="imported_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-10-18T15:30:00Z"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         nullable=true,
 *         example="2025-10-19T12:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         nullable=true,
 *         example="2025-10-19T12:00:00Z"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="PackageImportRequest",
 *     type="object",
 *     @OA\Property(
 *         property="packages",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/PackageImportItem")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="PackageCollectionResponse",
 *     type="object",
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/PackageResource")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="PackageErrorResponse",
 *     type="object",
 *     @OA\Property(property="message", type="string", example="Detalle del error.")
 * )
 *
 * @OA\Schema(
 *     schema="PackageConflictResponse",
 *     type="object",
 *     @OA\Property(property="message", type="string", example="Packages already exist with these IDs."),
 *     @OA\Property(
 *         property="duplicate_ids",
 *         type="array",
 *         @OA\Items(type="string"),
 *         example={"PKG-0001","PKG-0002"}
 *     )
 * )
 */
class PackageSchemas
{
}
