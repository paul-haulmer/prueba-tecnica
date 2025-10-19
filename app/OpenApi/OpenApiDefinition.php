<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Logistics Packages API",
 *     description="Microservicio para importar, consultar, actualizar y eliminar paquetes.",
 * )
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Servidor principal"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="packages_apikey",
 *     type="apiKey",
 *     in="header",
 *     name="X-API-Key",
 *     description="Incluya la clave en el encabezado X-API-Key."
 * )
 */
class OpenApiDefinition
{
}
