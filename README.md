# BestWeb Laravel Assessment

Product management application built with Laravel 13, MySQL and Blade. Includes an admin dashboard, public product pages and an authenticated REST API. Excel exports use Laravel-Excel.

## Setup

Requires Docker Desktop with Linux containers. Run the commands from the project directory.

1. Start Docker Desktop and generate the local environment files.

   PowerShell:

   ```powershell
   docker run --rm --mount "type=bind,source=$($PWD.Path),target=/app" --workdir /app php:8.3-cli-alpine php docker/setup.php
   ```

   Bash (macOS/Linux):

   ```bash
   docker run --rm --mount "type=bind,source=$(pwd),target=/app" --workdir /app php:8.3-cli-alpine php docker/setup.php
   ```

2. Open `.env`. Keep the generated `ADMIN_PASSWORD`, or set a password of at least 12 characters. The default login email is `admin@example.test`; change `ADMIN_EMAIL` if needed. Existing non-empty settings are preserved.

3. Build and start the containers. This installs dependencies, runs migrations and creates the admin account and categories.

   ```sh
   docker compose up --build --detach --wait --wait-timeout 900
   ```

4. Open http://127.0.0.1:8000/login and sign in using `ADMIN_EMAIL` and `ADMIN_PASSWORD` from `.env`.

| Page | URL |
| --- | --- |
| Product catalog | http://127.0.0.1:8000/ |
| Admin dashboard | http://127.0.0.1:8000/admin/products |
| Swagger documentation | http://127.0.0.1:8000/api/documentation |

A new database has no products. Add them through the dashboard. Stop the containers with `docker compose down`; database data is retained.

## Tests

```sh
docker compose exec -T app composer test
```

Tests use the separate `bestweb_testing` MySQL database, created during Docker setup. The suite covers authentication, permissions, product validation, filtering, pagination, updates, soft deletion, bulk deletion and Excel exports.


## API

In Swagger, call `POST /api/tokens` with the admin email and password. Copy `data.token` into **Authorize**, then call the product endpoints. Tokens expire after 24 hours.

For other clients, send `Accept: application/json` and `Authorization: Bearer <token>`. Send JSON request bodies with `Content-Type: application/json`.

| Method | Endpoint | Description |
| --- | --- | --- |
| POST | `/api/tokens` | Login with `email` and `password` |
| DELETE | `/api/tokens/current` | Revoke the current token |
| GET | `/api/products` | List products; filters: `category_id`, `enabled`; pagination: `page`, `per_page` |
| POST | `/api/products` | Create a product |
| GET | `/api/products/{id}` | Get product details |
| DELETE | `/api/products/{id}` | Soft-delete a product |
| DELETE | `/api/products/bulk` | Bulk delete using `{"ids":[1,2]}` |

Product fields: `name`, `category_id`, `description`, `price`, `stock`, `enabled`. Responses include `category_id` and `category_name`. Full request and response examples are available in Swagger and `public/openapi.json`. The Swagger viewer needs internet access to load its assets.


