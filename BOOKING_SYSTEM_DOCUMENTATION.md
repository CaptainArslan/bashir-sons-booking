# Bus Ticket Booking System - Backend Architecture Documentation

## 🎯 Overview

A complete Laravel 12 backend architecture for a bus ticket booking system with online/counter bookings, real-time seat selection, and trip expense management.

## 📋 Features Implemented

✅ Online, Counter, and Phone Bookings
✅ Real-time Seat Locking with Redis
✅ Trip Management & Lifecycle
✅ Expense Tracking per Trip
✅ Employee Route Permissions
✅ Automated Job Scheduling
✅ Real-time Broadcasting Events
✅ Stop-to-Stop Fare Calculation
✅ Gender-Aware Seating Support

---

## 🏗️ Architecture Components

### 1. Database Migrations

All migrations are located in `database/migrations/`:

- **trips** - Daily trip instances
- **bookings** - Main booking records
- **booking_seats** - Individual seat details with passenger info
- **seat_locks** - Temporary seat holds (auditing)
- **expenses** - Trip-wise expense records
- **employee_routes** - Employee-route permissions

### 2. Enums (`app/Enums/`)

- **BookingTypeEnum**: Online, Counter, Phone
- **BookingStatusEnum**: Pending, Confirmed, Cancelled, Completed, NoShow
- **TripStatusEnum**: Pending, Scheduled, Boarding, Ongoing, Completed, Cancelled, Delayed
- **ExpenseTypeEnum**: Fuel, Toll, DriverPay, Maintenance, Refreshment, Parking, Miscellaneous
- **SeatLockTypeEnum**: Temporary (5min), PhoneHold (30min), Reserved

### 3. Models (`app/Models/`)

#### Core Models
- **Trip** - Daily trip instances with relationships to timetables, routes, and buses
- **Booking** - Main booking record with auto-generated booking numbers
- **BookingSeat** - Individual seat assignments with passenger details
- **SeatLock** - Audit trail for seat locks
- **Expense** - Trip expenses with type validation

#### Relationships
```php
Trip hasMany Booking, Expense, SeatLock
Trip belongsTo Route, Bus, Timetable
Booking hasMany BookingSeat
Booking belongsTo Trip, User, RouteStop (from/to)
User hasMany Booking, Expense
User belongsToMany Route (employee_routes)
```

### 4. Service Classes (`app/Services/`)

#### **TripService**
- `getOrCreateTrip()` - Get or create trip for date/route
- `createTrip()` - Create new trip instance
- `assignBus()` - Assign bus to trip with conflict checking
- `startTrip()`, `completeTrip()`, `cancelTrip()` - Status management
- `getTripStatistics()` - Revenue, expenses, occupancy
- `generateTripsFromTimetables()` - Bulk trip generation

#### **SeatService** (Redis-based)
- `getAvailableSeats()` - Check seat availability for segment
- `lockSeat()` - Lock seat in Redis with TTL
- `releaseSeat()` - Release seat lock
- `getLockedSeats()` - Get all locked seats for trip
- `syncSeatLocks()` - Reconcile Redis ↔ Database
- `isSeatAvailable()` - Check specific seat availability

#### **BookingService**
- `createBooking()` - Create booking with seat locks
- `confirmBooking()` - Confirm and release locks
- `cancelBooking()` - Cancel and free seats
- `calculateFare()` - Stop-to-stop fare calculation
- `searchTrips()` - Find available trips
- `releasePhoneBookingsBeforeDeparture()` - Auto-release phone holds
- `validateEmployeeBooking()` - Check employee permissions

#### **ExpenseService**
- `addExpense()` - Add expense to trip (requires bus assignment)
- `updateExpense()`, `deleteExpense()` - Expense management
- `getTripExpensesSummary()` - Summary by type
- `getExpenseStatistics()` - Analytics

#### **TripLifecycleService**
- `processBoardingTrips()` - Mark trips as boarding
- `processStartingTrips()` - Auto-start trips
- `processCompletedTrips()` - Auto-complete trips
- `processDelayedTrips()` - Mark delayed trips
- `getTripsRequiringAttention()` - Admin alerts

### 5. Controllers (`app/Http/Controllers/`)

All controllers return structured JSON responses:
```json
{
    "status": "success|error",
    "message": "Human-readable message",
    "data": { ... }
}
```

#### **BookingController**
- `POST /v1/trips/search` - Search available trips
- `POST /v1/bookings` - Create booking
- `POST /v1/bookings/{id}/confirm` - Confirm booking
- `POST /v1/bookings/{id}/cancel` - Cancel booking
- `GET /v1/bookings/{id}` - Get booking details
- `POST /v1/bookings/calculate-fare` - Calculate fare

#### **TripController**
- `GET /v1/trips` - List trips
- `GET /v1/trips/{id}` - Get trip details
- `POST /v1/trips/{id}/assign-bus` - Assign bus (Admin)
- `POST /v1/trips/{id}/start` - Start trip (Admin)
- `POST /v1/trips/{id}/complete` - Complete trip (Admin)
- `GET /v1/trips/{id}/statistics` - Trip statistics
- `POST /v1/trips/generate-from-timetables` - Generate trips (Admin)

#### **ExpenseController**
- `POST /v1/expenses` - Add expense
- `PUT /v1/expenses/{id}` - Update expense
- `DELETE /v1/expenses/{id}` - Delete expense (Admin)
- `GET /v1/expenses/trip/{tripId}/summary` - Trip summary
- `GET /v1/expenses/statistics` - Statistics (Admin)

#### **SeatController**
- `GET /v1/seats/available` - Get available seats
- `POST /v1/seats/lock` - Lock seat
- `POST /v1/seats/release` - Release seat
- `GET /v1/seats/locked/{tripId}` - Get locked seats
- `POST /v1/seats/extend-lock` - Extend lock duration

### 6. Events (`app/Events/`)

All events implement `ShouldBroadcast` for real-time updates:

- **SeatLocked** - Broadcast when seat is locked
- **SeatReleased** - Broadcast when seat is released
- **TripBusAssigned** - Broadcast when bus is assigned

**Channel**: `trip.{tripId}`

### 7. Jobs (`app/Jobs/`)

Scheduled background jobs:

- **ReleasePhoneBookingsJob** - Release phone bookings 30min before departure
- **StartTripJob** - Auto-start trips at departure time
- **CompleteTripJob** - Auto-complete trips at arrival time
- **SyncSeatLocksJob** - Sync Redis with database every 5 minutes

### 8. Form Requests (`app/Http/Requests/`)

All use array-based validation with custom messages:

- **CreateBookingRequest** - Validate booking creation
- **CreateExpenseRequest** - Validate expense creation
- **UpdateExpenseRequest** - Validate expense updates
- **AssignBusToTripRequest** - Validate bus assignment
- **SearchTripsRequest** - Validate trip search

### 9. Policies (`app/Policies/`)

Role-based authorization:

- **BookingPolicy** - Booking permissions
- **TripPolicy** - Trip management permissions
- **ExpensePolicy** - Expense management permissions

### 10. Factories & Seeders

#### Factories (`database/factories/`)
- TripFactory - With states: `pending()`, `scheduled()`, `ongoing()`, `completed()`, `withoutBus()`
- BookingFactory - With states: `confirmed()`, `pending()`, `cancelled()`, `online()`, `counter()`, `phone()`
- BookingSeatFactory
- SeatLockFactory - With states: `active()`, `expired()`, `released()`
- ExpenseFactory - With states: `fuel()`, `toll()`, `driverPay()`

#### Seeders (`database/seeders/`)
- TripSeeder - Creates 50 trips with various statuses
- BookingSeeder - Creates 45 bookings with seats
- ExpenseSeeder - Creates 70 expenses

---

## 🔧 Implementation Guide

### 1. Run Migrations

```bash
php artisan migrate
```

### 2. Seed Database (Optional)

```bash
php artisan db:seed --class=TripSeeder
php artisan db:seed --class=BookingSeeder
php artisan db:seed --class=ExpenseSeeder
```

### 3. Configure Redis

Ensure Redis is running and configured in `.env`:

```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 4. Configure Broadcasting

For real-time seat updates, configure Laravel Echo with Pusher/Soketi:

```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-key
PUSHER_APP_SECRET=your-secret
PUSHER_APP_CLUSTER=mt1
```

### 5. Schedule Jobs

Add to `routes/console.php` or `app/Console/Kernel.php`:

```php
// Release phone bookings 30 minutes before departure
Schedule::job(new ReleasePhoneBookingsJob(30))->everyMinute();

// Sync seat locks every 5 minutes
Schedule::job(new SyncSeatLocksJob())->everyFiveMinutes();

// Process trip lifecycle
Schedule::job(new StartTripJob())->everyMinute();
Schedule::job(new CompleteTripJob())->everyFiveMinutes();
```

Start the scheduler:
```bash
php artisan schedule:work
```

### 6. Configure Queues

For background job processing:

```bash
php artisan queue:work
```

---

## 📊 Business Logic Flow

### Booking Flow

1. **Search Trips**
   - User searches by route, date, from/to stops
   - System finds trips with available seats
   - Returns trips with fare calculation

2. **Lock Seats**
   - User selects seats
   - System locks seats in Redis (5 min TTL)
   - Broadcasts `SeatLocked` event

3. **Create Booking**
   - System creates booking (status: pending)
   - Creates booking_seats records
   - Locks persisted in database for audit

4. **Confirm Booking**
   - User confirms (payment, etc.)
   - Status changes to confirmed
   - Seat locks released (no longer temporary)

5. **Auto-Release Phone Bookings**
   - Job runs every minute
   - Releases phone bookings not confirmed 30min before departure

### Trip Lifecycle

1. **Trip Creation**
   - Auto-created when first booking is made
   - Or generated from timetables in bulk
   - Status: `pending` (no bus assigned)

2. **Bus Assignment**
   - Admin assigns bus to trip
   - Status: `scheduled`
   - Broadcasts `TripBusAssigned` event

3. **Boarding**
   - Auto-status change 30min before departure
   - Status: `boarding`

4. **Departure**
   - Auto-status change at departure time
   - Status: `ongoing`

5. **Completion**
   - Auto-status change at arrival time
   - Status: `completed`

### Expense Management

- Expenses can only be added when bus is assigned
- Cannot add/edit expenses for completed trips (Admin can delete)
- Supports fuel, toll, driver pay, maintenance, refreshment, parking, misc
- Receipt number required for fuel, toll, maintenance

---

## 🔐 Security & Permissions

### Role-Based Access

- **Super Admin / Admin**: Full access
- **Employee**: 
  - Can book for assigned routes only
  - Can book from/after their terminal only
  - Can view bookings and add expenses
- **Customer**: Can create and manage own bookings

### Employee Route Restrictions

Enforced in `BookingService::validateEmployeeBooking()`:
1. Employee must be assigned to route
2. Employee can only book from their terminal onwards
3. Cannot book before their terminal in route sequence

---

## 🚀 API Endpoint Summary

### Public Endpoints
- `POST /api/v1/trips/search` - Search trips
- `POST /api/v1/bookings/calculate-fare` - Calculate fare
- `GET /api/v1/seats/available` - Check seat availability

### Authenticated Endpoints
All require `auth:sanctum` middleware.

See `routes/api.php` for complete endpoint list with role restrictions.

---

## 🧪 Testing

### Example Usage with Factories

```php
// Create a trip with bookings
$trip = Trip::factory()
    ->scheduled()
    ->create();

$booking = Booking::factory()
    ->confirmed()
    ->for($trip)
    ->create();

BookingSeat::factory()
    ->count(2)
    ->for($booking)
    ->create();
```

### Example Service Usage

```php
// Search trips
$results = $bookingService->searchTrips([
    'route_id' => 1,
    'date' => '2025-10-27',
    'from_stop_id' => 1,
    'to_stop_id' => 5,
    'passengers' => 2
]);

// Create booking
$booking = $bookingService->createBooking([
    'route_id' => 1,
    'departure_date' => '2025-10-27',
    'from_stop_id' => 1,
    'to_stop_id' => 5,
    'type' => 'online',
    'seats' => [
        [
            'seat_number' => '1A',
            'seat_row' => '1',
            'seat_column' => 'A',
            'passenger_name' => 'John Doe',
            'passenger_age' => 30,
            'passenger_gender' => 'male'
        ]
    ]
]);
```

---

## 📈 Key Features Explained

### Real-Time Seat Locking

- Uses Redis for temporary locks (5 min default)
- Database for audit trail
- Auto-sync every 5 minutes
- Prevents double-booking
- Broadcasting for live updates

### Stop-to-Stop Fare Calculation

- Calculates fare based on route segments
- Aggregates fares from consecutive stops
- Supports per-passenger pricing
- Discount support built-in

### Trip Auto-Generation

- Generate trips from timetables for date ranges
- Prevents duplicate trips
- Maintains timetable relationship

### Expense Validation

- Only after bus assignment
- Receipt required for certain types
- Cannot modify completed trip expenses

---

## 🎨 Code Standards

✅ **PSR-12** compliant (enforced by Pint)
✅ **Laravel 12** syntax
✅ **Eloquent relationships** with type hints
✅ **Form Request validation** (no inline validation)
✅ **Service layer** for business logic
✅ **Policy-based authorization**
✅ **Enum usage** for fixed values
✅ **Factory states** for testing

---

## 📝 Notes

1. **Redis is required** for seat locking functionality
2. **Broadcasting** is optional but recommended for real-time updates
3. **Queue workers** should be running for background jobs
4. **Scheduler** should be running for automated tasks
5. All **monetary values** are stored as decimals with 2 decimal places
6. **Soft deletes** enabled on trips, bookings, and expenses

---

## 🔜 Next Steps

1. Run migrations: `php artisan migrate`
2. Configure Redis connection
3. Set up queue workers: `php artisan queue:work`
4. Set up scheduler: `php artisan schedule:work`
5. Configure broadcasting (optional)
6. Seed test data (optional)
7. Run tests: `php artisan test`

---

## 📚 Additional Resources

- **Migrations**: `database/migrations/2025_10_26_*`
- **API Routes**: `routes/api.php`
- **Service Classes**: `app/Services/`
- **Models**: `app/Models/`
- **Controllers**: `app/Http/Controllers/`

---

**Built with Laravel 12, following best practices for maintainability, scalability, and security.**

