# Logistics Packages Microservice (LPM)

Microservicio en Laravel 12 para gestionar paquetes logísticos: importar lotes desde sistemas externos, consultar con filtros, actualizar estados y eliminar paquetes pendientes. Toda la API expone JSON sobre HTTP y se protege mediante una API Key fija.

## Requisitos
- PHP 8.2+
- Composer 2+
- SQLite 3
- Docker y Docker Compose

## Ejecución con Docker
1. Crear el `.env` (si no existe) y define `APP_KEY` o déjalo vacío para generarlo dentro del contenedor.
2. Levantar el microservicio:
   ```bash
   docker compose up --build -d
   ```

   - API: `http://localhost:8000/api`
   - Swagger UI: `http://localhost:8000/api/documentation`

3. En caso de querer generar la clave de la app desde Docker ejecutr lo siguiente:
   ```bash
   docker compose exec app php artisan key:generate
   ```

Los volúmenes declarados (`vendor` y `storage`) permiten que las dependencias PHP y los archivos generados persistan entre reinicios, mientras que el código fuente se monta directamente desde el host para un flujo de trabajo en local.

Comandos útiles adicionales:
```bash
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan test
```

## Endpoints principales
Todos los endpoints requieren el encabezado `X-API-Key: haulmer-2025-apikey` (se exponen en este MD únicamente porque es una prueba técnica, en contextos normales no agregaria registros de la apikey real).

| Metodo | Ruta                         | Descripción                                        |
|--------|------------------------------|----------------------------------------------------|
| POST   | `/api/packages/import`       | Importa una lista de paquetes                     |
| GET    | `/api/packages`              | Lista paquetes, permite filtrar por estado y prioridad |
| PATCH  | `/api/packages/{id}/status`  | Actualiza el estado de un paquete                 |
| DELETE | `/api/packages/{id}`         | Elimina un paquete (solo si está pendiente)       |

### Ejemplo de importación
```bash
curl -X POST http://127.0.0.1:8000/api/packages/import \
  -H "Content-Type: application/json" \
  -H "X-API-Key: haulmer-2025-apikey" \
  -d '{
    "packages": [
      {
        "id": "PKG-9001",
        "priority": "high",
        "status": "pending",
        "imported_at": "2025-10-18T12:00:00Z"
      }
    ]
  }'
```

### Filtros disponibles
- `GET /api/packages?status=pending`
  ```bash
  curl -X GET "http://127.0.0.1:8000/api/packages?status=pending" \
    -H "Content-Type: application/json" \
    -H "X-API-Key: haulmer-2025-apikey"
  ```
- `GET /api/packages?priority=high`
  ```bash
  curl -X GET "http://127.0.0.1:8000/api/packages?priority=high" \
    -H "Content-Type: application/json" \
    -H "X-API-Key: haulmer-2025-apikey"
  ```
- `GET /api/packages?status=pending&priority=high` (combinando parámetros)
  ```bash
  curl -X GET "http://127.0.0.1:8000/api/packages?status=pending&priority=high" \
    -H "Content-Type: application/json" \
    -H "X-API-Key: haulmer-2025-apikey"
  ```

## Documentación API
- Swagger UI: `GET /api/documentation`, acá esta la documentación en caso de que otras empresas/equipos requieran saber envío de peticiones y respuestas del microserverico.
- JSON generado: `storage/api-docs/api-docs.json`

## Acceso a la base de datos
La aplicación utiliza SQLite. El archivo se encuentra en `database/database.sqlite`.

- **Desde el host** (macOS/Linux):
  ```bash
  sqlite3 database/database.sqlite
  sqlite> .tables
  sqlite> SELECT * FROM packages;
  ```
- **Dentro del contenedor** (útil si levantaste Docker):
  ```bash
  docker compose exec app sqlite3 database/database.sqlite
  ```

Puedes salir de la consola SQLite escribiendo `.exit`.

## Pruebas (dentro del contenedor)
```bash
php artisan test
```

Los tests cubren la lógica del servicio (importaciones, transiciones y eliminaciones) y la capa HTTP (autorización, filtros, flujos casos feliz y errores).

## Seeder iniciales
`DatabaseSeeder` ejecuta `PackageSeeder`, creando paquetes de ejemplo con distintas combinaciones de prioridad y estado al ejecutar `php artisan migrate --seed` o al levantar los contenedores.

## Diseño y arquitectura
- **Framework & stack**: Laravel 12 con SQLite para simplicidad y portabilidad. La documentación OpenAPI se genera mediante `l5-swagger`.
- **Capas**: Controladores finos delegan en `PackageService`, que encapsula reglas de negocio (validaciones de duplicados, transiciones y eliminación restringida). El `PackageRepository` abstrae el acceso a datos y evita dependencias directas con Eloquent desde la capa de servicio.
- **Dominio**: Enumeraciones nativas (`PackagePriority`, `PackageStatus`) mantienen consistencia y validaciones en formularios, servicios y esquemas. El modelo `Package` usa UUID como clave primaria y `imported_at` para ordenar consultas.
- **Seguridad**: Middleware `ApiKeyMiddleware` exige el header `X-API-Key` en todas las rutas de la API. La clave se parametriza por configuración y se documenta en Swagger.
- **Docs & DX**: Anotaciones OpenAPI en controladores e invariantes de dominio generan automáticamente Swagger UI y archivos JSON versionados.
- **DevOps**: Dockerfile multi-stage + docker-compose facilitan ejecución homogénea. El entrypoint garantiza migraciones, seeds y regeneración de documentación.
- **Calidad**: Suite de pruebas automatizadas (unitarias y de integración) cubre los escenarios críticos. Se usa `RefreshDatabase` para aislar casos y asegurar idempotencia.

## Scripts útiles (datasos)
- `php artisan migrate:fresh --seed` — restablece la base con datos demo.
- `php artisan l5-swagger:generate` — regenera la documentación tras cambios en anotaciones.
- `php artisan migrate --seed && php artisan test` — flujo rápido de verificación local.
