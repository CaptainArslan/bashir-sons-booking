# Bus Booking System - Complete Documentation

A comprehensive Laravel 12 bus ticket booking system with real-time seat selection, trip management, and expense tracking.

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Features](#features)
3. [Architecture](#architecture)
4. [Setup Instructions](#setup-instructions)
5. [Database Structure](#database-structure)
6. [Admin Panel](#admin-panel)
7. [Booking Flow](#booking-flow)
8. [Real-time Seat Selection](#real-time-seat-selection)
9. [Trip Management](#trip-management)
10. [Expense Management](#expense-management)
11. [Scheduler & Jobs](#scheduler--jobs)
12. [Testing](#testing)
13. [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

This is a complete bus ticket booking system built with Laravel 12, featuring:
- **Real-time seat selection** via Ably broadcasting
- **Multi-step booking flow** with passenger details
- **Trip lifecycle management** with automated jobs
- **Expense tracking** per trip
- **Employee route permissions**
- **Admin panel** for managing bookings, trips, and expenses

**Technology Stack:**
- Laravel 12
- PHP 8.2+
- MySQL/PostgreSQL
- Redis (for caching)
- Ably (for real-time broadcasting)
- Alpine.js & Tailwind CSS

---

## ✨ Features

### Booking System
- ✅ Online, Counter, and Phone bookings
- ✅ Real-time seat locking (10-minute duration)
- ✅ 45-seat bus layout with visual selection
- ✅ Segment-based booking (any stop to any stop)
- ✅ Gender-based seat selection with visual badges
- ✅ Passenger detail collection (Name, CNIC, Gender, Age, Phone)
- ✅ Multiple payment methods (Cash, Card, Mobile Wallet, Bank Transfer)
- ✅ Discount support
- ✅ Auto-release phone bookings 30 minutes before departure

### Real-time Features
- ✅ Instant seat updates via Ably WebSocket
- ✅ No polling - pure event-driven updates
- ✅ Live seat locking across multiple users
- ✅ Gender indicators on selected and locked seats
- ✅ Toast notifications for seat changes

### Trip Management
- ✅ Auto-creation on first booking
- ✅ Bus assignment with conflict checking
- ✅ Trip status lifecycle (Pending → Scheduled → Boarding → Ongoing → Completed)
- ✅ Occupancy tracking
- ✅ Revenue and profit calculation
- ✅ Bulk trip generation from timetables

### Expense Management
- ✅ Per-trip expense tracking
- ✅ Multiple expense types (Fuel, Toll, Driver Pay, Maintenance, etc.)
- ✅ Receipt number tracking
- ✅ Trip profitability analysis

### Admin Panel
- ✅ Modern, responsive UI with gradient headers
- ✅ Trip management dashboard
- ✅ Booking management with filters
- ✅ Expense tracking and reports
- ✅ Statistics cards
- ✅ Role-based access control

---

## 🏗️ Architecture

### Design Patterns
- **Service Layer Pattern** - Business logic encapsulation
- **Repository Pattern** - Through Eloquent models
- **Observer Pattern** - Laravel events
- **Factory Pattern** - Test data generation
- **Policy Pattern** - Authorization

### Directory Structure
```
app/
├── Console/Commands/          # Artisan commands
├── Enums/                     # Type-safe enumerations
├── Events/                    # Broadcasting events
├── Http/
│   ├── Controllers/Admin/     # Admin panel controllers
│   ├── Middleware/            # Custom middleware
│   └── Requests/              # Form request validation
├── Jobs/                      # Background jobs
├── Models/                    # Eloquent models
├── Policies/                  # Authorization policies
├── Services/                  # Business logic layer
└── Traits/                    # Reusable traits

database/
├── factories/                 # Model factories
├── migrations/                # Database migrations
└── seeders/                   # Database seeders

resources/
├── views/admin/               # Admin panel views
│   ├── bookings/
│   ├── trips/
│   └── expenses/
└── js/                        # Frontend JavaScript

routes/
├── web.php                    # Web routes
├── channels.php               # Broadcasting channels
└── console.php                # Scheduler configuration
```

### Service Layer
- **TripService** - Trip CRUD, bus assignment, statistics, auto-generation
- **SeatService** - Redis-based seat locking with database sync
- **BookingService** - Booking flow, fare calculation, employee validation
- **ExpenseService** - Expense management with trip validation
- **TripLifecycleService** - Automated trip status transitions

---

## 🚀 Setup Instructions

### 1. Installation

```bash
# Clone the repository
git clone <repository-url>
cd bashir-sons

# Install PHP dependencies
composer install

# Install NPM dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=bashir_sons
DB_USERNAME=root
DB_PASSWORD=

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed
```

### 2. Redis Configuration

Ensure Redis is running and configured in `.env`:

```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 3. Ably Broadcasting Setup

#### Get Ably API Keys

1. Sign up at [https://ably.com](https://ably.com)
2. Create a new app
3. Get your API key (format: `{app-id}.{key-id}:{key-secret}`)

#### Configure Environment

Add to `.env`:

```env
BROADCAST_CONNECTION=ably

# Ably Configuration
ABLY_KEY=your-app-id.key-id:key-secret

# For client-side (Vite)
VITE_ABLY_PUBLIC_KEY=your-app-id.key-id
```

#### Build Assets

```bash
npm run build
# or for development
npm run dev
```

### 4. Queue Configuration

```bash
# Development
php artisan queue:work --tries=3

# Production (use Supervisor)
# Create /etc/supervisor/conf.d/laravel-worker.conf
```

### 5. Scheduler Configuration

Add to `routes/console.php` (already configured):

```bash
# Development
php artisan schedule:work

# Production - Add to crontab
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### 6. Run the Application

```bash
# Using Laravel Herd
# Application available at: https://bashir-sons.test

# Or using Artisan
php artisan serve
# Application available at: http://localhost:8000
```

---

## 🗄️ Database Structure

### Core Tables

#### trips
- Daily trip instances
- Links to routes, buses, and timetables
- Status tracking (pending → scheduled → boarding → ongoing → completed)

#### bookings
- Main booking records with auto-generated booking numbers
- Type: online, counter, phone
- Status: pending, confirmed, cancelled, completed
- Payment tracking and contact information

#### booking_seats
- Individual seat assignments
- Passenger details (name, CNIC, gender, age, phone)
- Per-seat fare tracking

#### seat_locks (audit)
- Temporary seat holds for auditing
- 10-minute expiration

#### expenses
- Trip-wise expense records
- Types: Fuel, Toll, Driver Pay, Maintenance, Refreshment, Parking, Misc
- Receipt number tracking

#### employee_routes
- Employee-route permission mapping
- Terminal-based booking restrictions

### Key Relationships

```
Route (1) ──┬→ (many) RouteStops
            └→ (many) Trips

Trip (1) ───┬→ (many) Bookings
            └→ (many) Expenses

Booking (1) ─→ (many) BookingSeats

User ←──── booked_by_user_id ──→ Booking
RouteStop ←── from_stop_id ───┬→ Booking
RouteStop ←── to_stop_id ──────┘
```

---

## 🎨 Admin Panel

### Access URLs

**Base URL:** `https://bashir-sons.test/admin`

### Trip Management
- **All Trips:** `/admin/trips`
- **Trip Dashboard:** `/admin/trips/dashboard`
- **Requires Bus:** `/admin/trips/requires-bus`
- **View Trip:** `/admin/trips/{id}`

### Booking Management
- **All Bookings:** `/admin/bookings`
- **Create Booking:** `/admin/bookings/create`
- **View Booking:** `/admin/bookings/{id}`
- **Reports:** `/admin/bookings/reports`

### Expense Management
- **All Expenses:** `/admin/expenses`
- **Add Expense:** `/admin/expenses/create`
- **Edit Expense:** `/admin/expenses/{id}/edit`
- **Reports:** `/admin/expenses/reports`

### Controllers

#### TripManagementController
- List trips with filters (route, status, date, bus assignment)
- View trip details with statistics
- Assign/change bus
- Update trip status
- Start/Complete/Cancel trips
- Generate trips from timetables

#### BookingManagementController
- List bookings with filters (booking number, status, type, route)
- View booking details with passenger info
- Confirm pending bookings
- Cancel bookings with reason

#### ExpenseManagementController
- List expenses with filters (type, route, date range)
- Add/Edit/Delete expenses
- Trip-wise expense summary
- Expense reports and statistics

### UI Features
- ✅ Modern gradient headers (purple for trips, pink for bookings, orange for expenses)
- ✅ Statistics cards with large numbers
- ✅ Filter forms with dropdowns and date pickers
- ✅ Responsive tables with pagination
- ✅ Status badges with color coding
- ✅ Progress bars for occupancy
- ✅ Bootstrap modals for actions
- ✅ Empty states with friendly messages

---

## 🔄 Booking Flow

### Step 1: Search for Available Seats
**Route:** `/admin/bookings/create`

Employee selects:
- From Terminal
- To Terminal
- Departure Date

System actions:
1. Automatically loads available departure times
2. Shows smart date labels (Today, Tomorrow, MM/DD/YYYY)
3. Displays route details for each time slot
4. Creates or fetches existing trip for route + date

### Step 2: Select Seats
**Route:** `/admin/bookings/search` (POST)

Features:
- 45-seat bus layout (11 rows × 4 seats + 1 last row with 5 seats)
- Real-time seat availability via Ably
- 5 distinct seat states with visual indicators
- Gender selection modal for each seat
- Live fare calculation

**Seat States:**

1. **Available** - Light green (#f0fff4) with green border
   - Can be clicked to select
   - Hovers to solid green

2. **Your Selection** - Solid blue (#0d6efd)
   - Shows gender badge in top-right corner (♂/♀)
   - White circular badge with colored icon
   - Click again to deselect

3. **Locked by Others** - Gray (#e9ecef) with dashed border
   - Shows lock icon 🔒 in top-right
   - Shows gender icon (♂/♀) at bottom center
   - Tooltip shows who locked it
   - Updates in real-time via Ably

4. **Booked (Pending)** - Yellow (#fff3cd) with orange border
   - Shows hourglass icon ⏳
   - Payment pending
   - Cannot be selected

5. **Sold (Confirmed)** - Red (#dc3545)
   - Shows checkmark icon ✓
   - Payment confirmed
   - Cannot be selected

### Step 3: Enter Passenger Details
**Route:** `/admin/bookings/select-seats` (POST)

For each passenger:
- Full Name (required)
- CNIC (required, format: XXXXX-XXXXXXX-X)
- Gender (required)
- Age (optional)
- Phone (optional)

Contact person:
- Contact Name (required)
- Contact Phone (required)
- Contact Email (optional)
- Notes (optional)

Booking details:
- Booking Type (counter/phone)
- Payment Method (cash/card/mobile_wallet/bank_transfer)
- Payment Status (paid/pending)
- Discount Amount (optional)

### Step 4: Confirm Booking
**Route:** `/admin/bookings/store` (POST)

System actions:
1. Validates all passenger data
2. Re-checks seat availability (prevent race conditions)
3. Calculates final amount (total_fare - discount)
4. Creates `Booking` record with unique booking number
5. Creates `BookingSeat` records for each passenger
6. Sets `confirmed_at` if payment_status = 'paid'
7. Sets `reserved_until` for phone bookings (departure - 30 mins)
8. Redirects to booking details page

---

## 🔴 Real-time Seat Selection

### Overview
The system uses **Ably broadcasting** for instant seat updates across multiple users with **no polling**.

### Key Features

#### 1. Pure Real-time Updates
- ✅ No polling - zero server overhead
- ✅ WebSocket-based instant updates
- ✅ Broadcasts only when seats are locked/released
- ✅ Connection monitoring with alerts

#### 2. Gender Badges
- **Your selections:** Gender badge in top-right (♂ male / ♀ female)
- **Locked seats:** Gender icon at bottom center
- **Selection summary:** Shows "Seat X - Male" or "Seat X - Female"

#### 3. Broadcasting Events

**SeatLocked Event**
```php
Channel: trip.{tripId}
Event: .seat.locked

Payload: {
    trip_id: 1,
    seat_number: "12",
    from_stop_id: 5,
    to_stop_id: 10,
    gender: "male",
    user_name: "John Doe",
    session_id: "abc123...",
    timestamp: "2025-10-27T10:30:00.000Z"
}
```

**SeatReleased Event**
```php
Channel: trip.{tripId}
Event: .seat.released

Payload: {
    trip_id: 1,
    seat_number: "12",
    from_stop_id: 5,
    to_stop_id: 10,
    session_id: "abc123...",
    timestamp: "2025-10-27T10:31:00.000Z"
}
```

#### 4. Seat Lock Cache

Format:
```
Key: seat_lock:{trip_id}:{seat_number}:{from_stop_id}:{to_stop_id}

Value: {
    user_id: 1,
    user_name: "John Doe",
    session_id: "abc123...",
    gender: "male",
    locked_at: "2024-01-15 10:30:00"
}

TTL: 10 minutes
```

### API Endpoints

#### Lock Seat
**POST** `/admin/bookings/lock-seat`

```json
{
    "trip_id": 1,
    "from_stop_id": 1,
    "to_stop_id": 5,
    "seat_number": "12",
    "gender": "male"
}
```

#### Unlock Seat
**POST** `/admin/bookings/unlock-seat`

```json
{
    "trip_id": 1,
    "from_stop_id": 1,
    "to_stop_id": 5,
    "seat_number": "12"
}
```

#### Check Seats (Debugging)
**POST** `/admin/bookings/check-seats`

Returns locked seats with gender info and seat statuses (booked vs confirmed).

### Testing Real-time Updates

1. Open in **two browsers** (Chrome and Firefox/Incognito)
2. Select same trip, date, and terminals in both
3. **Browser 1:** Select a seat and choose gender
4. **Browser 2:** Watch seat immediately turn gray with lock icon
5. **Browser 1:** Deselect the seat
6. **Browser 2:** Watch seat turn green (available)
7. Check console logs for real-time events

---

## 🚍 Trip Management

### Trip Lifecycle

```
Pending ──→ Scheduled ──→ Boarding ──→ Ongoing ──→ Completed
   ↓             ↓           ↓            ↓            ↓
   └───────────────────────────────────────────→ Cancelled
                                                       ↓
                                                   Delayed
```

### Auto-Creation
- Trips are automatically created when first booking is made
- Initial status: `pending` (no bus assigned)
- Or generate in bulk from timetables

### Bus Assignment
- Admin assigns bus to trip
- Status changes to `scheduled`
- Conflict checking prevents double-booking
- Broadcasts `TripBusAssigned` event

### Status Transitions

1. **Pending → Scheduled**
   - Trigger: Bus assigned
   - Action: Manual (Admin)

2. **Scheduled → Boarding**
   - Trigger: 30 minutes before departure
   - Action: Automated (Job)

3. **Boarding → Ongoing**
   - Trigger: Departure time reached
   - Action: Automated (Job) or Manual

4. **Ongoing → Completed**
   - Trigger: Arrival time reached
   - Action: Automated (Job) or Manual

5. **Any → Cancelled**
   - Trigger: Admin cancellation
   - Action: Manual

### Trip Statistics
- Total bookings
- Total passengers
- Occupancy rate
- Total revenue
- Total expenses
- Profit/Loss

---

## 💰 Expense Management

### Expense Types
- **Fuel** - Requires receipt number
- **Toll** - Requires receipt number
- **Driver Pay**
- **Maintenance** - Requires receipt number
- **Refreshment**
- **Parking**
- **Miscellaneous**

### Business Rules
- ✅ Expenses can only be added when bus is assigned
- ✅ Cannot add/edit expenses for completed trips
- ✅ Admin can delete any expense
- ✅ Receipt number required for certain types

### Trip Expense Summary
- Total expenses by type
- Expense count
- Average expense per trip
- Profitability calculation (revenue - expenses)

---

## ⚙️ Scheduler & Jobs

### Scheduled Jobs

#### 1. SyncSeatLocksJob (Every 5 Minutes)
- Syncs Redis seat locks with database
- Releases expired locks
- Updates database records
- Cleans up stale Redis keys

#### 2. ReleasePhoneBookingsJob (Every Minute)
- Finds trips departing in next 30 minutes
- Cancels pending phone bookings
- Releases associated seat locks

#### 3. StartTripJob (Every Minute)
- Finds scheduled/boarding trips at departure time
- Checks if bus is assigned
- Changes status to "ongoing"

#### 4. CompleteTripJob (Every 5 Minutes)
- Finds ongoing trips past estimated arrival
- Changes status to "completed"

### Manual Triggers

```bash
# Test seat lock sync
php artisan tinker
>>> dispatch(new \App\Jobs\SyncSeatLocksJob());

# Test phone booking release
>>> dispatch(new \App\Jobs\ReleasePhoneBookingsJob(30));

# Test trip lifecycle
>>> dispatch(new \App\Jobs\StartTripJob());
>>> dispatch(new \App\Jobs\CompleteTripJob());
```

---

## 🧪 Testing

### Run Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/BookingTest.php

# Run with filter
php artisan test --filter=testBookingCreation
```

### Manual Testing Checklist

#### Booking Creation
- [ ] Can search for available trips
- [ ] Can select seats in real-time
- [ ] Locked seats appear immediately in other browsers
- [ ] Can enter passenger details
- [ ] Can complete booking
- [ ] Booking number generated correctly

#### Real-time Features
- [ ] Ably connection established
- [ ] Seats lock instantly across browsers
- [ ] Seats unlock instantly when released
- [ ] Toast notifications appear
- [ ] Gender badges display correctly

#### Trip Management
- [ ] Can view trip list with filters
- [ ] Can assign bus to trip
- [ ] Can update trip status
- [ ] Statistics display correctly
- [ ] Can generate trips from timetables

#### Expense Management
- [ ] Can add expense (only with bus assigned)
- [ ] Cannot edit completed trip expenses
- [ ] Can view trip expense summary
- [ ] Receipt validation works

---

## 🐛 Troubleshooting

### Echo/Ably Not Connecting

**Check browser console:**
```
⚠️ Echo is not defined. Real-time updates will not work.
```

**Solution:**
```bash
npm run build
# Refresh browser
php artisan config:clear
php artisan cache:clear
```

**Verify environment variables:**
```env
BROADCAST_CONNECTION=ably
ABLY_KEY=your-key-here
VITE_ABLY_PUBLIC_KEY=your-public-key-here
```

### Seats Not Updating in Real-time

1. **Check console logs:**
   - Look for: "✅ Real-time seat updates active via Ably"
   - Check for connection errors

2. **Check network tab:**
   - WebSocket connection to `realtime-pusher.ably.io`
   - Status should be "101 Switching Protocols"

3. **Verify Ably dashboard:**
   - Check for active connections
   - Monitor message activity

4. **Check broadcasting config:**
   ```php
   // config/broadcasting.php
   'default' => env('BROADCAST_CONNECTION', 'ably'),
   ```

### Jobs Not Running

1. **Check scheduler:**
   ```bash
   php artisan schedule:work
   ```

2. **Check queue workers:**
   ```bash
   php artisan queue:work
   ```

3. **Check Redis connection:**
   ```bash
   php artisan tinker
   >>> Redis::ping()
   // Should return "PONG"
   ```

4. **Check failed jobs:**
   ```bash
   php artisan queue:failed
   php artisan queue:retry all
   ```

### Seat Lock Issues

**Lock expires too quickly:**
```php
// Adjust in BookingController.php
Cache::put($lockKey, $lockValue, now()->addMinutes(10));
// Change 10 to desired minutes
```

**Stale locks:**
```bash
# Clear all seat locks
php artisan tinker
>>> Cache::flush()
```

### Permission Errors

**Check role assignment:**
```bash
php artisan tinker
>>> $user = User::find(1);
>>> $user->roles()->sync([1]); // Admin role
>>> $user->givePermissionTo('access admin panel');
```

---

## 📝 Code Quality

### Standards
- ✅ PSR-12 compliant (enforced by Laravel Pint)
- ✅ Laravel 12 syntax
- ✅ Type hints on all methods
- ✅ Eloquent relationships with return types
- ✅ Form Request validation (no inline validation)
- ✅ Service layer for business logic
- ✅ Policy-based authorization
- ✅ Enum usage for fixed values

### Run Code Formatter

```bash
# Format all files
vendor/bin/pint

# Format specific directory
vendor/bin/pint app/Services

# Check without fixing
vendor/bin/pint --test
```

---

## 📊 Statistics

### Files Created
- **52 new/updated files**
- **~5,000 lines of code**

### Database
- **6 new tables** (trips, bookings, booking_seats, seat_locks, expenses, employee_routes)
- **35 migrations**

### Features
- **29 web routes** (API removed - web-only application)
- **5 Enums**
- **5 Models** (+ 4 updated)
- **5 Services**
- **3 Admin Controllers**
- **4 Jobs**
- **3 Events**
- **5 Form Requests**
- **3 Policies**
- **8 Blade Views**

---

## 🔐 Security

### Authentication
- Laravel Sanctum for API (if needed in future)
- Session-based for web

### Authorization
- Role-based (Super Admin, Admin, Employee)
- Policy-based permissions
- Employee route restrictions
- Terminal-based booking limits

### Data Protection
- CSRF protection
- SQL injection prevention (Eloquent ORM)
- XSS prevention (Blade escaping)
- Soft deletes for data recovery

---

## 🚀 Deployment

### Production Checklist

- [ ] Configure `.env` for production
- [ ] Set `APP_DEBUG=false`
- [ ] Configure proper database credentials
- [ ] Set up Redis for caching and queues
- [ ] Configure Ably credentials
- [ ] Set up queue workers with Supervisor
- [ ] Configure cron for scheduler
- [ ] Run migrations
- [ ] Build frontend assets (`npm run build`)
- [ ] Set up SSL certificate
- [ ] Configure backups
- [ ] Set up logging and monitoring

### Supervisor Configuration

Create `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path-to-project/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/laravel-worker.log
```

### Crontab

```cron
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 📞 Support

For issues or questions:
- Check Laravel logs: `storage/logs/laravel.log`
- Check browser console for frontend errors
- Verify Ably dashboard for connectivity issues
- Review this documentation

### Useful Links
- [Laravel Documentation](https://laravel.com/docs)
- [Ably Documentation](https://ably.com/documentation)
- [Laravel Broadcasting](https://laravel.com/docs/broadcasting)

---

## 📜 License

This project is proprietary software developed for Bashir & Sons.

---

**Last Updated:** October 27, 2025  
**Version:** 2.0  
**Laravel:** 12  
**PHP:** 8.2+
