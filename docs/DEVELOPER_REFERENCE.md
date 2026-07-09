# TerraStay Hotel Booking System
## Developer Reference — Flows & Database Design

**Project:** Hotel Inventory & Booking System  
**Stack:** Laravel 13 · PHP 8.3 · Sanctum · Blade · MySQL/SQLite · Redis  
**Repo:** BookingApp (hotel-booking-temp)

---

## 1. What This Project Is

A hotel inventory and **date-aware availability search** system with:

- **Web UI** (session auth, Blade + Bootstrap 5)
- **JSON API** (Sanctum token auth)
- **Shared service layer** — business logic is never duplicated between web and API

Default login (seeded): `admin@terrastay.com` / `password`

---

## 2. Brief Spec vs Implementation

| Brief / spec idea | What we built | Notes |
|-------------------|---------------|-------|
| `rooms` table with `available_rooms` integer | `room_types` + `room_units` + `bookings` | Availability is **computed per date range**, not a static counter |
| Hotel CRUD | Web full CRUD + API list/create | API has no update/delete/show |
| Room CRUD | Web full CRUD + API create | Room = type + physical unit numbers |
| Search by city + dates | `SearchService` with overlap algorithm | Half-open interval `[checkin, checkout)` |
| Sanctum API auth | ✅ Token create/revoke | Web uses session guard |
| Cache search results | ✅ Redis, 60s TTL + version bump | Portable invalidation without cache tags |
| Docker | ✅ Laravel Sail (PHP 8.3, MySQL 8, Redis) | `compose.yaml` + `sail.ps1` on Windows |

### Enhancements beyond the brief

| Enhancement | Why it matters |
|-------------|----------------|
| **UUID v4 primary keys** | Safer public IDs; consistent across domain tables |
| **Soft deletes** | Hotels, room types, units, bookings recoverable |
| **Audit columns** | `created_by`, `updated_by`, `deleted_by` on all domain models |
| **Spatie activity log** | Full change history + `/activity` admin page |
| **Repository pattern** | Services depend on interfaces; Eloquent hidden behind contracts |
| **Row-level locking** | `lockForUpdate()` on booking create prevents double-booking |
| **Countries / cities lookup** | Seeded reference data for form dropdowns |
| **Dashboard charts** | Booking trends, occupancy %, top hotels/cities |
| **Detail/show pages** | Hotels, room types, bookings with audit panels |
| **Rate limiting** | Login, search, API group throttles |
| **44 automated tests** | Unit + feature coverage on SQLite in-memory |

---

## 3. Architecture

```
HTTP Request
    │
    ▼
┌─────────────────────────────────────────────────────────┐
│  Middleware (auth, throttle, CSRF on web)               │
└─────────────────────────────────────────────────────────┘
    │
    ▼
┌──────────────────┐     ┌──────────────────┐
│  Web Controllers │     │  API Controllers │
│  (redirect/view) │     │  (JSON + status) │
└────────┬─────────┘     └────────┬─────────┘
         │                        │
         └──────────┬─────────────┘
                    ▼
         ┌──────────────────────┐
         │  Form Requests       │  ← validation + authorize()
         └──────────┬───────────┘
                    ▼
         ┌──────────────────────┐
         │  Services            │  ← business logic
         │  Hotel · RoomType    │
         │  Booking · Search    │
         │  Dashboard · Cache   │
         └──────────┬───────────┘
                    ▼
         ┌──────────────────────┐
         │  Repositories        │  ← DB queries only
         │  (Eloquent impl.)    │
         └──────────┬───────────┘
                    ▼
         ┌──────────────────────┐
         │  Eloquent Models     │
         │  + relationships     │
         └──────────────────────┘
```

**Key folders**

| Path | Purpose |
|------|---------|
| `app/Http/Controllers/Web/` | Blade pages, redirects, flash messages |
| `app/Http/Controllers/Api/` | JSON responses, HTTP status codes |
| `app/Http/Requests/` | Validation rules |
| `app/Http/Resources/` | API response shape |
| `app/Services/` | Business logic |
| `app/Repositories/` | Data access |
| `app/Models/AppModel.php` | Base model: UUID, soft delete, audit, activity log |

---

## 4. Database Design

### 4.1 Entity Relationship (core domain)

```
┌──────────┐       ┌─────────────┐       ┌─────────────┐       ┌──────────┐
│  users   │       │   hotels    │       │ room_types  │       │bookings  │
│ (bigint) │◄──────│   (uuid)    │──1─*──│   (uuid)    │──1─*──│  (uuid)  │
└──────────┘ audit │ name        │       │ hotel_id FK │       │ room_type│
                  │ city        │       │ name        │       │ room_unit│
                  │ country     │       │ price/night │       │ dates    │
                  │ rating      │       │ max_guests  │       │ guest_*  │
                  └─────────────┘       └──────┬──────┘       └────┬─────┘
                                               │ 1                 │
                                               │ *                 │ 0..1
                                        ┌──────▼──────┐       ┌──────▼──────┐
                                        │ room_units  │◄──────│  (unit FK)  │
                                        │   (uuid)    │       └─────────────┘
                                        │ room_number │
                                        │ status      │
                                        └─────────────┘

┌───────────┐  1──*  ┌────────┐
│ countries │────────│ cities │   ← lookup tables for forms (hotels store city/country as strings)
│  (uuid)   │        │ (uuid) │
└───────────┘        └────────┘
```

### 4.2 Table reference

#### `hotels`
| Column | Type | Notes |
|--------|------|-------|
| `id` | UUID | PK |
| `name` | string | |
| `city` | string | indexed |
| `country` | string | |
| `rating` | tinyint 1–5 | |
| `created_by`, `updated_by`, `deleted_by` | bigint nullable | → users.id |
| `deleted_at` | timestamp | soft delete |

**Indexes:** `city`, `(city, rating)`

---

#### `room_types` (migration name: `create_rooms_table`)
| Column | Type | Notes |
|--------|------|-------|
| `id` | UUID | PK |
| `hotel_id` | UUID | FK → hotels, cascade delete |
| `name` | string | e.g. "Deluxe King" |
| `price_per_night` | decimal(10,2) | |
| `max_occupancy` | smallint | |
| audit + soft delete | | same pattern |

---

#### `room_units`
| Column | Type | Notes |
|--------|------|-------|
| `id` | UUID | PK |
| `room_type_id` | UUID | FK → room_types, cascade |
| `room_number` | string | e.g. "101", "102" |
| `status` | string | `available` · `maintenance` · `blocked` |
| audit + soft delete | | |

**Unique:** `(room_type_id, room_number)`  
**Index:** `(room_type_id, status)`

---

#### `bookings`
| Column | Type | Notes |
|--------|------|-------|
| `id` | UUID | PK |
| `booking_reference` | string | unique, e.g. `BK-20260801-A3F2` |
| `room_type_id` | UUID | FK → room_types |
| `room_unit_id` | UUID nullable | FK → room_units, null on delete |
| `checkin_date` | date | |
| `checkout_date` | date | checkout day is **free** |
| `guests` | smallint | |
| `guest_name`, `guest_email` | string | |
| `guest_phone` | string nullable | |
| `status` | string | `confirmed` · `cancelled` |
| `total_price` | decimal(10,2) | snapshot at booking time |
| audit + soft delete | | |

**Availability indexes:**
- `(room_type_id, status, checkin_date, checkout_date)`
- `(room_unit_id, status, checkin_date, checkout_date)`

---

#### `countries` / `cities`
Reference data for dropdowns. Hotels still store `city` and `country` as plain strings (denormalized for search simplicity).

| `cities` | |
|----------|--|
| `country_id` | FK → countries |
| `name` | unique per country |

Both have audit + soft delete columns (added in later migration).

---

#### `activity_log` (Spatie)
| Column | Purpose |
|--------|---------|
| `subject_id/type` | What changed (morph) |
| `causer_id/type` | Who changed it (morph → User) |
| `event` | created / updated / deleted |
| `properties` | JSON old/new values |
| `log_name` | grouping filter |

---

#### `users` + `personal_access_tokens`
- `users`: standard Laravel auth (bigint PK)
- `personal_access_tokens`: Sanctum API tokens (morph to User)

---

### 4.3 Base model behaviour (`AppModel`)

All domain models extend `AppModel`:

1. **UUID v4** primary key (`HasVersion4Uuids`)
2. **Soft deletes** (`SoftDeletes`)
3. **Activity logging** — logs fillable attributes, dirty only
4. **Audit auto-stamp:**
   - `created_by` / `updated_by` on save (when authenticated)
   - `deleted_by` on soft delete

Relationships: `creator()`, `updater()`, `deleter()` → `User`

---

## 5. Application Flows

### 5.1 Web authentication (session)

```
Browser → GET /login → AuthController@showLogin → Blade form
Browser → POST /login → LoginRequest validates
         → Auth::attempt() → session regenerate
         → ActivityLogger "Logged in via web"
         → redirect /dashboard

POST /logout → ActivityLogger → Auth::logout()
            → session invalidate → redirect /login
```

- Middleware: `guest` on login, `auth` on all app pages
- CSRF protection on all POST/PUT/DELETE
- Rate limit: 5 attempts/min on login

---

### 5.2 API authentication (Sanctum)

```
POST /api/login → LoginRequest → Hash::check
              → 401 if fail
              → 200 { token, user } + activity log

Protected routes → Authorization: Bearer {token}
                 → auth:sanctum middleware

POST /api/logout → revoke current token → 204
```

---

### 5.3 Hotel CRUD

**Web:** list (filters: country, city, rating) → create/edit modal → show detail page  
**API:** `GET /api/hotels` (public, paginated) · `POST /api/hotels` (token, 201)

**Delete guard:** cannot delete hotel if it has room types (`ResourceInUseException` → 409)

```
HotelController → StoreHotelRequest → HotelService
               → HotelRepository → Hotel model
```

---

### 5.4 Room type CRUD

A "room" in the UI = **room type** (category) + **room units** (physical numbers).

**Create flow:**
```
StoreRoomTypeRequest → RoomTypeService::create()
  → insert room_types row
  → bulk insert room_units (parsed from "101, 102, 103")
  → SearchResultCache::bump()
```

**Delete guard:** cannot delete room type with existing bookings.

---

### 5.5 Search availability

```
SearchRequest (city, checkin, checkout, guests)
    │
    ▼
SearchService::search()
    │
    ├─► Build cache key: search:{version}:{md5(params)}
    ├─► Cache miss? Run query:
    │       HotelRepository::availableInCity()
    │       For each room type:
    │         BookingService::availableUnitsForStay()
    │         Filter by max_occupancy >= guests
    │         Calculate nights × price_per_night
    └─► Return hotels with available room types + unit numbers
```

**Overlap rule (half-open `[checkin, checkout)`):**

```
A unit is BLOCKED if a confirmed booking exists where:
  existing.checkin_date  < requested.checkout_date
  AND existing.checkout_date > requested.checkin_date
```

- Cancelled bookings do **not** block availability
- Only units with `status = available` are searchable
- Checkout day is free for the next guest

**Cache:** 60 second TTL. On any booking or inventory change → `SearchResultCache::bump()` increments version key (orphans old cache entries).

---

### 5.6 Booking create (overbooking-safe)

```
StoreBookingRequest → BookingService::create() [DB::transaction]
    │
    ├─► RoomTypeRepository::findForUpdate()     ← row lock
    ├─► Validate guests <= max_occupancy
    ├─► resolveUnit():
    │     If room_unit_id provided → lock that unit, verify free
    │     Else → auto-pick first free unit for stay
    ├─► Generate reference: BK-{Ymd}-{RANDOM4}
    ├─► total_price = price_per_night × nights
    ├─► Insert booking (status: confirmed)
    └─► SearchResultCache::bump()
```

**Cancel:** sets `status = cancelled` (does not hard-delete). Frees the unit for future searches.

---

### 5.7 Dashboard

```
DashboardController → aggregates:
  - Hotel count, room type count, unit count
  - Confirmed bookings, average rating
  - Occupancy % (units with active confirmed stay today / total units)

DashboardService::charts() → JSON for Chart.js:
  - 6-month booking + revenue trend
  - Status doughnut (confirmed vs cancelled)
  - Top 5 hotels by bookings
  - Top 5 cities by hotel count
```

---

### 5.8 Activity log

**Automatic:** Spatie `LogsActivity` on all `AppModel` children — logs fillable field changes.

**Manual:** `ActivityLogger` on web/API login and logout.

**UI:** `GET /activity` — paginated log with filters (log name, event type), shows causer + subject.

---

## 6. API Endpoints Summary

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/login` | public | Returns `{ token, user }` |
| POST | `/api/logout` | token | Revoke token (204) |
| GET | `/api/hotels` | public | Paginated list |
| POST | `/api/hotels` | token | Create hotel (201) |
| POST | `/api/rooms` | token | Create room type + units (201) |
| GET | `/api/search` | public | Availability search |
| GET | `/api/room-types/{id}/available-units` | public | Free units for dates |
| GET | `/api/bookings` | token | Paginated bookings |
| POST | `/api/bookings` | token | Create booking (201) |
| DELETE | `/api/bookings/{id}` | token | Cancel booking (204) |

**Rate limits:** API group 60/min · login 10/min · search 30/min

---

## 7. Web Pages Summary

| Route | Page |
|-------|------|
| `/login` | Split-screen login with password toggle |
| `/dashboard` | Stats tiles + Chart.js charts |
| `/hotels` | List, filters, create/edit modal, show detail |
| `/rooms` | Room types list, create/edit, show detail |
| `/bookings` | Booking list, create form, show detail |
| `/search` | Date-aware availability search + results |
| `/activity` | Audit log viewer |

---

## 8. Running the Project

### Local (SQLite + Redis)
```bash
composer install && cp .env.example .env
php artisan key:generate && php artisan migrate:fresh --seed
php artisan serve   # http://127.0.0.1:8000
```
Windows: `.\run-local.ps1 up`

### Docker (Laravel Sail)
```bash
# merge .env.docker.example into .env
.\sail.ps1 build && .\sail.ps1 setup   # Windows
# http://localhost:8080/login
```

### Tests
```bash
php artisan test          # 44 tests, SQLite in-memory
.\sail.ps1 test           # same suite inside container
```

---

## 9. Test Coverage Map

| Test file | What it verifies |
|-----------|------------------|
| `AvailabilityTest` (unit) | Overlap logic, checkout-day-free rule |
| `SearchAvailabilityTest` | Search results, pricing, guest filter |
| `BookingTest` | Create, 422 when full, cache invalidation, cancel |
| `AuthTest` | Web session + API token flows |
| `InventoryTest` | Delete guards, API city filter |
| `AuditAndSoftDeleteTest` | UUID, audit columns, soft delete |
| `ActivityLogTest` | Spatie logging on CRUD + auth |
| `ViewPagesTest` | Show pages, dashboard charts, auth gate |

---

## 10. File Quick Reference

| Concern | Primary files |
|---------|---------------|
| Search + cache | `app/Services/SearchService.php`, `app/Services/Search/SearchResultCache.php` |
| Booking logic | `app/Services/BookingService.php` |
| Availability query | `app/Repositories/Eloquent/EloquentRoomUnitRepository.php` |
| API errors | `app/Exceptions/ApiExceptionRenderer.php` |
| Repo bindings | `app/Providers/RepositoryServiceProvider.php` |
| Sail Docker | `compose.yaml`, `sail.ps1` |
| Seed data | `database/seeders/LocationSeeder.php`, `DatabaseSeeder.php` |

---

*Generated for TerraStay / hotel-booking-temp — Laravel 13 interview submission.*
