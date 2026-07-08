# Hotel Inventory & Booking System

A small hotel inventory and availability system built with **Laravel 13**, **Sanctum**, and
**Blade + Bootstrap 5**, using a layered **Controller → Service → Repository** architecture.

It ships both a JSON API (token auth) and a Blade web UI (session auth) that share the same
service layer — business logic is never duplicated between them.

## Highlights

- **Date-aware availability** — how many units of a room are free is derived per date range
  from a real `bookings` table, not stored as a static counter.
- **Layered architecture** — thin controllers, logic in services, data access behind
  repository interfaces bound in `RepositoryServiceProvider`.
- **Sanctum** tokens for the API, session guard for the web UI.
- **UUID v4** primary keys, **soft deletes**, and a `created_by / updated_by / deleted_by`
  audit trail on every inventory table via a shared `AppModel`.
- **Overbooking-safe** booking writes (transaction + `lockForUpdate`).
- **Cached search** with version-based invalidation that works on the file/array cache driver.
- Domain exceptions mapped to correct HTTP status codes in one place.
- **32 tests** (unit + feature) green on SQLite in-memory.

## Availability model

`rooms.total_rooms` holds the physical inventory of a room type. Each confirmed booking sits in
the `bookings` table with a check-in/check-out range. For a requested range `[checkin, checkout)`
(the checkout day is free), a booking overlaps when:

```
existing.checkin_date < requested.checkout_date
AND existing.checkout_date > requested.checkin_date
```

Available units are `total_rooms - (busiest single night in the range)`. Rooms with `0` units,
or a `max_occupancy` below the party size, are excluded from search results.
`total_price = price_per_night × nights`.

## Local setup (SQLite)

```bash
composer install
cp .env.example .env       # Windows: copy .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

Open http://localhost:8000 and sign in with the seeded admin:

**`admin@example.com` / `password`**

## API

Base prefix `/api`, JSON only. Protected routes need `Authorization: Bearer <token>`.

| Method | Endpoint        | Auth   | Description                                    |
|--------|-----------------|--------|------------------------------------------------|
| POST   | `/api/login`    | public | Returns `{ token, user }` (401 on bad creds)   |
| POST   | `/api/logout`   | token  | Revokes the current token (204)                |
| GET    | `/api/hotels`   | public | Paginated list; filters `city`, `rating`       |
| POST   | `/api/hotels`   | token  | Create a hotel (201)                           |
| POST   | `/api/rooms`    | token  | Create a room type (201)                       |
| GET    | `/api/search`   | public | Availability for `city` + date range + `guests`|
| POST   | `/api/bookings` | token  | Create a booking (201, 422 if unavailable)     |

Rate limits: `throttle:60,1` across the group, `throttle:10,1` on login, `throttle:30,1` on search.

```bash
# Login and capture the token
TOKEN=$(curl -s -X POST http://localhost:8000/api/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}' \
  | php -r 'echo json_decode(stream_get_contents(STDIN), true)["token"];')

# Search availability (public)
curl "http://localhost:8000/api/search?city=Dubai&checkin_date=2026-08-01&checkout_date=2026-08-04&guests=2" \
  -H "Accept: application/json"
```

Import `postman_collection.json` — the requests capture the token and UUIDs into collection
variables, so run them in order: **Login → Create Hotel → Create Room → Create Booking**.

## Web pages

| Route        | Description                                              |
|--------------|---------------------------------------------------------|
| `/login`     | Session login form                                      |
| `/dashboard` | Totals for hotels, rooms, bookings, and average rating  |
| `/hotels`    | Create / edit / delete hotels + city & rating filter    |
| `/rooms`     | Create / edit / delete rooms + hotel & name filter      |
| `/bookings`  | Create / cancel bookings                                |
| `/search`    | Availability search form + results                      |

Deletes are dependency-checked: a hotel with rooms, or a room with bookings, cannot be removed
and the UI shows a clear message instead.

## Docker (MySQL + Redis)

```bash
docker compose up -d --build
docker compose exec app php artisan migrate:fresh --seed
```

App: http://localhost:8080. For Docker set in `.env`:

```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=hotel_booking
DB_USERNAME=hotel
DB_PASSWORD=secret
CACHE_STORE=redis
REDIS_HOST=redis
```

## Tests & style

```bash
php artisan test          # 32 tests on SQLite in-memory
php vendor/bin/pint --test # PSR-12 code style
```

## Structure

```
app/
  Exceptions/               # Domain exceptions + API exception renderer
  Http/Controllers/Api/     # JSON API controllers
  Http/Controllers/Web/     # Blade controllers
  Http/Requests/            # Validation + typed accessors
  Http/Resources/           # API response shapes
  Models/                   # AppModel base + Hotel / Room / Booking / User
  Repositories/Contracts/   # Repository interfaces
  Repositories/Eloquent/    # Eloquent implementations
  Services/                 # HotelService / RoomService / BookingService / SearchService
  Providers/                # RepositoryServiceProvider binds contracts
```
