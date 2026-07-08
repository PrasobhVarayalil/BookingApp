# Hotel Inventory & Booking System

A small hotel inventory and availability-search system built with **Laravel 13**, **Sanctum**,
and **Blade + Bootstrap 5**, using a clean layered architecture
(**Controller → Service → Repository**).

It exposes both a **JSON API** (token auth) and a **Blade web UI** (session auth) that share the
same service layer — business logic is never duplicated between them.

---

## Highlights

- **Room types + physical room numbers** — inventory is split into `room_types` (Deluxe King)
  and `room_units` (101, 102, 103). Bookings assign a specific unit.
- **Date-aware availability** — a unit is free only when it has no overlapping confirmed
  booking for the entire requested stay.
- **Auto or manual room assignment** — leave `room_unit_id` blank to auto-assign the first
  free unit, or pass a unit id to pick a specific room number.
- **Layered architecture** — thin controllers, logic in services, data access behind
  repository interfaces bound in `RepositoryServiceProvider`.
- **Sanctum** tokens for the API, session guard for the web UI.
- **UUID v4** primary keys, **soft deletes**, and audit columns via a shared `AppModel`.
- **Overbooking-safe** booking writes (transaction + `lockForUpdate`).
- **Cached search** with version-based invalidation.
- **44 tests** (unit + feature) green on SQLite in-memory.

---

## Tech stack

| Concern    | Choice                                                      |
| ---------- | ----------------------------------------------------------- |
| Framework  | Laravel 13 (PHP 8.3+)                                       |
| Auth       | Laravel Sanctum (tokens for API, session for web)           |
| Database   | SQLite locally; MySQL 8 via Laravel Sail                  |
| Keys       | UUID v4 primary keys + soft deletes + audit columns         |
| Frontend   | Blade + Bootstrap 5, Tom Select & Bootstrap Icons (CDN)     |
| Cache      | Redis (search results, 60s TTL + version invalidation)      |
| Container  | Laravel Sail (PHP 8.3 + MySQL 8 + Redis)                    |
| Code style | PSR-12 via Laravel Pint                                     |

---

## Architecture

```
HTTP (API / Web)
   │
Controllers  ──►  Form Requests (validation)
   │
Services        HotelService · RoomTypeService · BookingService · SearchService
   │            (business logic, availability algorithm, caching)
Repositories    *RepositoryInterface  ─bind→  Eloquent*Repository
   │
Eloquent Models  Hotel · RoomType · RoomUnit · Booking · User
```

Key folders:

```
app/
  Http/Controllers/{Api,Web}/   Http/Requests/   Http/Resources/
  Services/   Services/Search/SearchResultCache.php
  Repositories/{Contracts,Eloquent}/
  Models/   Providers/RepositoryServiceProvider.php   Exceptions/
```

---

## Design note — spec mapping & date-aware availability

The brief models availability as a static `rooms.available_rooms` integer. Since search accepts
check-in/check-out dates, I extended that idea so availability is expressed **for a specific date
range** rather than as a single counter:

| Brief spec              | This implementation                                      |
| ----------------------- | -------------------------------------------------------- |
| `rooms` table           | `room_types` (category) + `room_units` (physical rooms)  |
| `available_rooms` field | Computed per stay from confirmed bookings                |
| `Hotel hasMany Rooms`   | `Hotel hasMany RoomType` → `hasMany RoomUnit`            |
| `POST /api/rooms`       | Creates a room type + unit numbers (API route unchanged)   |

**Overlap rule (half-open intervals `[checkin, checkout)` — checkout day is free):**

```
existing.checkin_date < requested.checkout_date
AND existing.checkout_date > requested.checkin_date
```

A room type appears in search when at least one `available` unit has zero overlaps for the full
stay. `total_price = price_per_night × nights`.

---

## Caching & invalidation

Each search is cached for **60s** via `Cache::remember`, keyed by a hash of the normalised query
params (`SearchService` + `App\Services\Search\SearchResultCache`).

Invalidation uses a **monotonic version number** baked into the cache key rather than cache tags,
because the default `file`/`array` drivers do not support tagging. Booking writes call
`SearchResultCache::bump()`, which increments the version so all previously cached search results
are orphaned and recomputed on the next request (they also expire naturally on their 60s TTL).

> Running on **Redis** (as in the Docker setup) you could switch to real cache tags for more
> surgical invalidation; the version-key approach is the portable default.

---

## Prerequisites

| Tool | Local dev | Laravel Sail (Docker) |
| ---- | --------- | --------------------- |
| PHP 8.3+ | required | included in container |
| Composer 2 | required | optional (runs in container) |
| Redis | required (`CACHE_STORE=redis`) | included in container |
| SQLite | default DB driver | — |
| Docker Desktop | optional | required (with WSL 2 on Windows) |

**Default admin** (seeded): `admin@terrastay.com` / `password`

---

## Installation

Pick one path. Do **not** mix SQLite and Sail MySQL settings in `.env` at the same time — use `.env.example` for local and merge `.env.docker.example` when switching to Sail.

### Option A — Local (no Docker)

Best for day-to-day coding on the host machine. Uses **SQLite** + **Redis on `127.0.0.1:6379`**.

```bash
composer install
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

Open **http://127.0.0.1:8000/login**.

**Redis** must be running (search caching depends on it):

```bash
# one-off Docker Redis
docker run -d --name terrastay-redis -p 6379:6379 redis:alpine
```

**Windows helper** — starts portable Redis + `artisan serve` in one command:

```powershell
.\run-local.ps1 up       # start Redis + dev server
.\run-local.ps1 status   # check both
.\run-local.ps1 fresh    # migrate:fresh --seed + start
.\run-local.ps1 down     # stop Redis + server
```

### Option B — Laravel Sail (Docker)

Uses [Laravel Sail](https://laravel.com/docs/sail): PHP 8.3, MySQL 8, and Redis in containers. App is served on **port 8080**.

| Service       | Purpose   | Host port |
| ------------- | --------- | --------- |
| laravel.test  | App + web | **8080**  |
| mysql         | Database  | **3307**  |
| redis         | Cache     | **6380**  |

**1 — Environment**

```bash
cp .env.example .env
# merge Sail overrides from .env.docker.example into .env
```

Required Sail keys (also in `.env.docker.example`):

```env
APP_URL=http://localhost:8080
APP_PORT=8080
DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=hotel_booking
DB_USERNAME=sail
DB_PASSWORD=password
CACHE_STORE=redis
REDIS_HOST=redis
REDIS_CLIENT=predis
FORWARD_DB_PORT=3307
FORWARD_REDIS_PORT=6380
WWWGROUP=1000
WWWUSER=1000
```

**2 — Build, start, bootstrap**

Linux / macOS / WSL:

```bash
./vendor/bin/sail build
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed
```

Windows PowerShell (use `sail.ps1` — `./vendor/bin/sail` does **not** work in native PowerShell):

```powershell
.\sail.ps1 build
.\sail.ps1 up
.\sail.ps1 setup
```

(`docker.ps1` is an alias for `sail.ps1`.)

Open **http://localhost:8080/login**.

**Handy Sail commands**

| Task | Linux/macOS/WSL | Windows PowerShell |
| ---- | --------------- | ------------------ |
| Start | `./vendor/bin/sail up -d` | `.\sail.ps1 up` |
| Stop | `./vendor/bin/sail down` | `.\sail.ps1 down` |
| Tests | `./vendor/bin/sail test` | `.\sail.ps1 test` |
| Pint | `./vendor/bin/sail pint --test` | `.\sail.ps1 pint` |
| Shell | `./vendor/bin/sail shell` | `.\sail.ps1 shell` |
| Logs | `./vendor/bin/sail logs -f` | `.\sail.ps1 logs` |

> Sail replaces a hand-rolled Nginx + PHP-FPM Compose stack for standard Laravel DX. Production would still use separate web/PHP services behind a reverse proxy.

### Switching between local and Sail

1. **Local → Sail:** merge `.env.docker.example` into `.env`, then `.\sail.ps1 setup`.
2. **Sail → Local:** restore SQLite keys from `.env.example` (`DB_CONNECTION=sqlite`, `REDIS_HOST=127.0.0.1`), then `php artisan migrate:fresh --seed`.

---

## Getting started — quick reference

The sections above are the full install guide. Short version:

**Local:** `composer install` → copy `.env` → `key:generate` → `migrate:fresh --seed` → `php artisan serve` → http://127.0.0.1:8000/login

**Sail:** merge `.env.docker.example` → `.\sail.ps1 setup` (Windows) or `./vendor/bin/sail up -d` + migrate (Linux) → http://localhost:8080/login

The seeder creates hotels across Dubai, London, Paris, Tokyo, and Mumbai with room types, unit
numbers, and sample bookings so search results vary by date.

---

## API reference

Base prefix `/api`, JSON only. Protected routes need `Authorization: Bearer <token>`.

| Method | Endpoint                              | Auth   | Description                                     |
| ------ | ------------------------------------- | ------ | ----------------------------------------------- |
| POST   | `/api/login`                          | public | Returns `{ token, user }` (401 on bad creds)    |
| POST   | `/api/logout`                         | token  | Revokes the current token (204)                 |
| GET    | `/api/hotels`                         | public | Paginated list; filters `city`, `rating`        |
| POST   | `/api/hotels`                         | token  | Create a hotel (201)                            |
| POST   | `/api/rooms`                          | token  | Create a room type + unit numbers (201)         |
| GET    | `/api/search`                         | public | Availability for city + date range + guests   |
| GET    | `/api/room-types/{id}/available-units`| public | Free room numbers for a stay                  |
| GET    | `/api/bookings`                       | token  | Paginated booking list                          |
| POST   | `/api/bookings`                       | token  | Create a booking (201, 422 if unavailable)      |
| DELETE | `/api/bookings/{id}`                  | token  | Cancel a booking (204)                          |

**Rate limits:** `throttle:60,1` across the API group, `throttle:10,1` on login,
`throttle:30,1` on search.

### Example — search

```bash
curl "http://localhost:8000/api/search?city=Dubai&checkin_date=2026-08-01&checkout_date=2026-08-04&guests=2" \
  -H "Accept: application/json"
```

```json
{
  "data": [{
    "hotel": { "id": "uuid", "name": "Burj Marina Resort", "city": "Dubai", "country": "United Arab Emirates", "rating": 5 },
    "nights": 3,
    "rooms": [{
      "id": "uuid", "name": "Deluxe King", "price_per_night": "220.00",
      "max_occupancy": 2, "available_units": 5, "available_room_numbers": ["101","102"],
      "total_price": "660.00"
    }]
  }],
  "meta": { "city": "Dubai", "checkin_date": "2026-08-01", "checkout_date": "2026-08-04", "guests": 2, "nights": 3 }
}
```

> IDs are **UUID v4** strings (not integers). Use port **8080** when running via Docker.

---

## Web pages

| Route       | Description                                                               |
| ----------- | ------------------------------------------------------------------------- |
| `/login`    | Session login form with inline validation                                 |
| `/dashboard`| Stats: total hotels, total rooms, room types, bookings, average rating    |
| `/hotels`   | Add/edit/delete hotels + country/city filter + pagination                 |
| `/rooms`    | Add/edit/delete room types (searchable hotel dropdown) + pagination       |
| `/bookings` | Create/cancel bookings with guest details + live price summary            |
| `/search`   | Availability search form + results with totals                          |

Deletes are dependency-checked: a hotel with room types, or a room type with bookings, cannot be
removed and the UI shows a clear message instead.

---

## Tests & code style

```bash
php artisan test                 # 44 tests on SQLite in-memory (local)
./vendor/bin/pint --test         # check PSR-12 style
./vendor/bin/pint                # auto-fix style
```

**In Sail / Docker:**

```bash
./vendor/bin/sail test           # Linux/macOS/WSL
.\sail.ps1 test                  # Windows PowerShell
```

Verified: **44 tests, 109 assertions** pass locally and inside the Sail container.

The suite covers availability (no bookings, fully booked, partial overlap, occupancy filtering,
pricing), auth (web redirect, API token, 401 on protected routes), the booking flow (creation,
422 when full, cache invalidation, cancellation), inventory delete guards, activity logging,
detail/view pages, dashboard charts, and audit-trail / soft-delete behaviour on the base model.

**CI** (`.github/workflows/`):

| Workflow | Runs on push/PR |
| -------- | --------------- |
| `ci.yml` | PHPUnit + Pint on the host (SQLite) |
| `docker.yml` | Full Sail build, migrate, test, and smoke check on port 8080 |

---

## Postman

Import both files from the project root:

- `postman_collection.json`
- `postman_environment.json` (vars: `base_url`, `token`, `hotel_id`, `room_type_id`, `booking_id`)

`base_url` defaults to `http://localhost:8000/api` (local run); change to
`http://localhost:8080/api` when running via Sail.

Run the requests in order — **Login → Create Hotel → Create Room Type → Search → Create Booking**.
Test scripts capture the returned token and UUIDs into collection/environment variables so the
chain works without pasting IDs by hand.

---

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| `connection refused` on Redis (local) | Start Redis: `docker run -d -p 6379:6379 redis:alpine` or `.\run-local.ps1 up` |
| `./vendor/bin/sail` fails on Windows PowerShell | Use `.\sail.ps1` instead (Sail's bash script needs WSL/Linux) |
| Docker "engine not running" | Open Docker Desktop, wait for **Engine running** |
| `wsl --update` needs admin | Run elevated: `wsl --update --web-download` |
| Local `artisan serve` DB errors after Sail | `.env` still points at `DB_HOST=mysql` — switch back to SQLite (see Installation) |
| First Sail build slow / fails | Needs internet; retry `.\sail.ps1 build` |

---

## What I'd add for production

- **Authorization policies / roles** (admin vs. staff) rather than "any authenticated user".
- **Redis cache tags** for targeted search invalidation instead of the version-key fallback.
- **Observability**: structured logging, request IDs, and metrics on search latency.
- **API niceties**: offset pagination, sorting, OpenAPI spec, idempotency keys on booking POST.
- **Static analysis** (PHPStan/Larastan) and CI running tests + Pint on every push (CI included).
- **Email confirmations** for bookings and guest communication.

---

## Structure

```
app/
  Exceptions/               # Domain exceptions + API exception renderer
  Http/Controllers/Api/     # JSON API controllers
  Http/Controllers/Web/     # Blade controllers
  Http/Requests/            # Validation + typed accessors
  Http/Resources/           # API response shapes
  Models/                   # AppModel + Hotel / RoomType / RoomUnit / Booking
  Repositories/Contracts/   # Repository interfaces
  Repositories/Eloquent/    # Eloquent implementations
  Services/                 # RoomTypeService / BookingService / SearchService
  Providers/                # RepositoryServiceProvider binds contracts
```
