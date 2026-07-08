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
- **33 tests** (unit + feature) green on SQLite in-memory.

---

## Tech stack

| Concern    | Choice                                                      |
| ---------- | ----------------------------------------------------------- |
| Framework  | Laravel 13 (PHP 8.3+)                                       |
| Auth       | Laravel Sanctum (tokens for API, session for web)           |
| Database   | SQLite locally; MySQL 8 via Docker                          |
| Keys       | UUID v4 primary keys + soft deletes + audit columns         |
| Frontend   | Blade + Bootstrap 5, Tom Select & Bootstrap Icons (CDN)     |
| Cache      | Database/file locally; Redis in Docker                      |
| Container  | Docker Compose (PHP-FPM + Nginx + MySQL + Redis)            |
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

## Getting started — Normal (no Docker)

**Requirements:** PHP 8.3+, Composer 2, SQLite (default) or MySQL.

```bash
composer install
cp .env.example .env       # Windows: copy .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

Open http://localhost:8000 and sign in with the seeded admin:

| Email               | Password   |
| ------------------- | ---------- |
| admin@terrastay.com | password   |

The seeder creates hotels across Dubai, London, Paris, Tokyo, and Mumbai with room types, unit
numbers, and sample bookings so search results vary by date.

---

## Getting started — Docker

Services defined in `docker-compose.yml`:

| Service | Image        | Purpose            | Host port |
| ------- | ------------ | ------------------ | --------- |
| app     | PHP 8.3 FPM  | Laravel app        | —         |
| nginx   | nginx:alpine | Web server         | **8080**  |
| mysql   | mysql:8.0    | Database           | **3307**  |
| redis   | redis:alpine | Cache              | **6380**  |

**1 — Create the env file and point it at Docker services**

```bash
cp .env.example .env
```

Set these keys in `.env`:

```env
APP_URL=http://localhost:8080
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=hotel_booking
DB_USERNAME=hotel
DB_PASSWORD=secret
CACHE_STORE=redis
REDIS_HOST=redis
```

**2 — Build and start**

```bash
make build
make up
make setup
```

Open **http://localhost:8080** and log in at `/login`.

**Handy Make commands**

```bash
make test        # run the test suite
make pint        # check code style
make pint-fix    # auto-fix code style
make migrate     # re-seed database
make down        # stop containers
```

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
php artisan test                 # 33 tests on SQLite in-memory
./vendor/bin/pint --test         # check PSR-12 style
./vendor/bin/pint                # auto-fix style
```

The suite covers availability (no bookings, fully booked, partial overlap, occupancy filtering,
pricing), auth (web redirect, API token, 401 on protected routes), the booking flow (creation,
422 when full, cache invalidation, cancellation), inventory delete guards, and audit-trail /
soft-delete behaviour on the base model.

CI runs tests + Pint on every push via `.github/workflows/ci.yml`.

---

## Postman

Import both files from the project root:

- `postman_collection.json`
- `postman_environment.json` (vars: `base_url`, `token`, `hotel_id`, `room_type_id`, `booking_id`)

`base_url` defaults to `http://localhost:8000/api` (no-Docker run); change to
`http://localhost:8080/api` if you run via Docker.

Run the requests in order — **Login → Create Hotel → Create Room Type → Search → Create Booking**.
Test scripts capture the returned token and UUIDs into collection/environment variables so the
chain works without pasting IDs by hand.

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
