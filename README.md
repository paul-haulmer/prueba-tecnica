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

La ejecución creará 2 contenedoses:
1. logistics-app: Contiene PHP-FPM y ejecuta Laravel (migraciones, seeds, artisan). No expone los puertos directamente, solo atiende peticiones fastCGI.
2. logistics-nginx: Actua como servidor web "frontal", escucha en 80 (mapeado al 8000 del host) y reenvía las solicitudes PHP al socket/host app:9000.

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
docker exec -it logistics-app bash
composer install --no-interaction --prefer-dist
php artisan test
```

Los tests cubren la lógica del servicio (importaciones, transiciones y eliminaciones) y la capa HTTP (autorización, filtros, flujos casos feliz y errores). Se ejecta el --prefer-dist para que el ccontenedor encuentre el PHPUnit, y el --no-interaction es para que no pregunte nada por consola (asi nos evitamos el proceso manual de estar aceptando o rechazando cosas)

## Seeder iniciales
`DatabaseSeeder` ejecuta `PackageSeeder`, creando paquetes de ejemplo con distintas combinaciones de prioridad y estado al ejecutar `php artisan migrate --seed` o al levantar los contenedores.

## Diseño y arquitectura
- **Framework & stack**: Laravel 12 con SQLite para simplicidad y portabilidad. La documentación OpenAPI se genera mediante `l5-swagger`. Se eligió esta versión porque es la más actual y estable.
- **Capas**: Los controladores solo se encargan de lo básico y delegan la lógica al `PackageService`, que maneja las reglas del negocio (como evitar duplicados o controlar cuándo se puede eliminar o cambiar algo). El `PackageRepository` se encarga de hablar con la base de datos, así el servicio no depende directamente de Eloquent.
- **Seguridad**: Middleware `ApiKeyMiddleware` exige el header `X-API-Key` en todas las rutas de la API. La clave se parametriza por configuración y se documenta en Swagger para servicios que ocuparan el microservicio.
- **Docs & DX**: Anotaciones OpenAPI en controladores e invariantes de dominio generan automáticamente Swagger UI y archivos JSON versionados. Es importante manejar la documentación de los microservicios sobre todo si este va a ser útilizado en otras empresas.
- **DevOps**: El Dockerfile multi-stage y docker-compose hacen que todo se ejecute igual en cualquier entorno. El entrypoint se encarga de correr las migraciones, cargar los datos iniciales y regenerar la documentación automáticamente.
- **Calidad**: Suite de pruebas automatizadas (unitarias y de integración) cubre los escenarios críticos. Se usa `RefreshDatabase` para aislar casos y asegurar idempotencia.

## Scripts útiles (se deben ejecutar en logistics-app)
- `php artisan migrate:fresh --seed` — restablece la base con datos demo.
- `php artisan l5-swagger:generate` — regenera la documentación tras cambios en anotaciones.
- `php artisan migrate --seed && php artisan test` — ojo, aca para el test necesita el composer install --no-interaction --prefer-dist 
