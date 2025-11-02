# 🚌 Bashir Sons Bus Booking System

A comprehensive, production-ready bus ticketing and management system built with Laravel 12, featuring real-time seat booking, role-based access control, multi-terminal support, and advanced booking management.

---

## 📋 Table of Contents

1. [Project Overview](#project-overview)
2. [Technology Stack](#technology-stack)
3. [Key Features](#key-features)
4. [Installation & Setup](#installation--setup)
5. [Project Structure](#project-structure)
6. [Architecture & Design Patterns](#architecture--design-patterns)
7. [Database Schema](#database-schema)
8. [Core Modules](#core-modules)
9. [Permission & Role System](#permission--role-system)
10. [Booking System](#booking-system)
11. [API Documentation](#api-documentation)
12. [Services & Algorithms](#services--algorithms)
13. [Real-Time Features](#real-time-features)
14. [Development Guidelines](#development-guidelines)
15. [Testing](#testing)
16. [Deployment](#deployment)

---

## 🎯 Project Overview

**Bashir Sons Bus Booking System** is an enterprise-grade bus ticketing platform designed to manage:

- **Multi-terminal operations** with terminal-specific employee access
- **Real-time seat booking** with live seat map updates via WebSocket
- **Route & timetable management** for complex bus networks
- **Booking channels**: Counter, Phone, and Online bookings
- **Payment processing** with cash/card support and transaction tracking
- **Role-based permissions** with granular access control
- **Advance booking** support with configurable booking windows
- **Bus assignment** for multi-segment routes with different buses

### System Capabilities

- ✅ **Real-time seat availability** with conflict prevention
- ✅ **Multi-user synchronization** via Laravel Reverb WebSocket
- ✅ **Pessimistic row locking** to prevent race conditions
- ✅ **RouteStop-based booking** for accurate route segment tracking
- ✅ **Terminal-based access control** for employees
- ✅ **Comprehensive permission system** using Spatie Laravel Permission
- ✅ **DataTable integration** for efficient data management
- ✅ **Event-driven architecture** for seat locking/unlocking
- ✅ **Scheduled job processing** for booking expiration

---

## 🛠️ Technology Stack

### Backend
- **Framework**: Laravel 12.33.0
- **PHP**: 8.2.12
- **Database**: MySQL
- **Real-Time**: Laravel Reverb (WebSocket)
- **Queue**: Redis (for scheduled jobs and broadcasting)
- **Permission**: Spatie Laravel Permission 6.21
- **DataTables**: Yajra Laravel DataTables 12.0
- **2FA**: Pragmarx Google2FA Laravel 2.3

### Frontend
- **Template Engine**: Blade
- **JavaScript**: Vanilla JavaScript + jQuery
- **CSS Framework**: Bootstrap 5
- **Real-Time**: Laravel Echo
- **Build Tool**: Vite
- **CSS**: Tailwind CSS 3.4.18

### Development Tools
- **Code Formatter**: Laravel Pint 1.25.1
- **Testing**: Pest 3.8.4 + PHPUnit 11.5.33
- **Debugging**: Laravel Debugbar 3.16 (dev)
- **Package Management**: Composer + NPM

---

## ✨ Key Features

### 1. Real-Time Booking Console
- Interactive 44-seat map with visual status indicators
- Live seat updates via WebSocket (Laravel Reverb)
- Gender selection per seat
- Automatic seat locking/unlocking
- Multi-user synchronization without page refresh

### 2. Multi-Channel Booking System
- **Counter**: Immediate confirmation with payment collection
- **Phone**: Hold status with auto-expiration (15 min before departure)
- **Online**: Future integration ready

### 3. Role-Based Access Control
- **Super Admin**: Full system access (bypasses all checks)
- **Admin**: Complete management access
- **Employee**: Terminal-restricted access
- **Customer**: Public booking access

### 4. Terminal Management
- Multiple terminals per city
- Terminal-specific employee assignments
- Terminal-based route filtering
- Counter operations per terminal

### 5. Route & Timetable Management
- Complex route definitions with multiple stops
- Timetable creation with departure/arrival times
- Auto-trip generation from timetables
- RouteStop sequence management

### 6. Bus Assignment System
- Multi-segment route support
- Different bus assignment per segment
- Driver and host assignment
- Bus facility management

### 7. Payment Management
- Fare calculation (base fare, discount, tax)
- Payment method tracking (cash/card)
- Amount received vs. return calculation
- Transaction ID for non-cash payments

---

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.2.12 or higher
- Composer
- Node.js and NPM
- MySQL 5.7+ or MariaDB 10.3+
- Redis (for queues and caching)
- Laravel Herd (for local development)

### Step 1: Clone Repository
```bash
git clone <repository-url>
cd bashir-sons
```

### Step 2: Install Dependencies
```bash
composer install
npm install
```

### Step 3: Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bashir_sons
DB_USERNAME=root
DB_PASSWORD=

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Broadcasting (Laravel Reverb)
BROADCAST_DRIVER=reverb
REVERB_APP_ID=your_app_id
REVERB_APP_KEY=your_app_key
REVERB_APP_SECRET=your_app_secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

### Step 4: Database Setup
```bash
php artisan migrate
php artisan db:seed  # If seeders exist
```

### Step 5: Build Frontend Assets
```bash
npm run build
# Or for development
npm run dev
```

### Step 6: Start WebSocket Server
```bash
php artisan reverb:start
```

### Step 7: Start Development Server
```bash
php artisan serve
# Or use Laravel Herd (auto-configured)
```

The application will be available at:
- **Local**: `http://bashir-sons.test` (Laravel Herd)
- **Development**: `http://localhost:8000`

---

## 📁 Project Structure

```
bashir-sons/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── CreateSampleTerminalsCommand.php
│   │       ├── CreateTerminalsCommand.php
│   │       ├── ExpireHolds.php           # Expires hold bookings
│   │       └── SetupPermissions.php     # Permission seeder
│   ├── Enums/                            # Enum definitions
│   │   ├── BookingStatusEnum.php
│   │   ├── ChannelEnum.php
│   │   ├── GenderEnum.php
│   │   ├── PaymentMethodEnum.php
│   │   └── ... (30+ enums)
│   ├── Events/                           # Broadcasting events
│   │   ├── SeatLocked.php
│   │   ├── SeatUnlocked.php
│   │   └── SeatConfirmed.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/                    # Admin controllers
│   │   │   │   ├── BookingController.php
│   │   │   │   ├── RouteController.php
│   │   │   │   ├── TimetableController.php
│   │   │   │   └── ... (20+ controllers)
│   │   │   ├── Auth/                     # Authentication
│   │   │   └── Customer/                 # Customer-facing
│   │   ├── Middleware/
│   │   │   ├── CheckPermission.php       # Permission middleware
│   │   │   └── TwoFactorPending.php      # 2FA middleware
│   │   └── Requests/                     # Form requests
│   ├── Mail/                              # Email notifications
│   ├── Models/                            # Eloquent models
│   │   ├── Booking.php
│   │   ├── Trip.php
│   │   ├── Route.php
│   │   ├── RouteStop.php
│   │   ├── TripStop.php
│   │   └── ... (25+ models)
│   ├── Policies/                          # Authorization policies
│   │   └── BookingPolicy.php
│   ├── Providers/
│   │   └── AppServiceProvider.php        # Gate configuration
│   ├── Services/                          # Business logic services
│   │   ├── AvailabilityService.php       # Seat availability logic
│   │   ├── BookingService.php            # Booking creation
│   │   └── TripFactoryService.php        # Trip generation
│   └── Traits/
│       └── HasPermissions.php             # Permission helpers
├── database/
│   ├── migrations/                        # Database migrations
│   └── seeders/                           # Database seeders
├── resources/
│   ├── views/
│   │   ├── admin/                         # Admin views
│   │   │   ├── bookings/
│   │   │   │   ├── console.blade.php     # Booking console
│   │   │   │   ├── index.blade.php
│   │   │   │   └── ...
│   │   │   └── layouts/
│   │   └── components/                    # Blade components
│   └── js/                                # JavaScript files
├── routes/
│   ├── web.php                            # Web routes
│   └── console.php                        # Console routes
└── tests/                                 # Pest tests
```

---

## 🏗️ Architecture & Design Patterns

### Architectural Patterns

#### 1. **Service Layer Pattern**
Business logic is separated into service classes:
- `AvailabilityService`: Handles seat availability calculations
- `BookingService`: Manages booking creation and validation
- `TripFactoryService`: Creates trips from timetables

#### 2. **Repository Pattern (Implicit)**
Eloquent models act as repositories with relationship methods.

#### 3. **Event-Driven Architecture**
Real-time updates via Laravel events:
- `SeatLocked`: When user selects a seat
- `SeatUnlocked`: When user deselects a seat
- `SeatConfirmed`: When booking is confirmed

#### 4. **Observer Pattern**
Model observers for automatic behavior (if implemented).

#### 5. **Middleware Chain**
Request flow through middleware:
```
Request → Authentication → Permission Check → 2FA Check → Controller
```

### Design Principles

1. **Single Responsibility**: Each service/controller has a specific purpose
2. **Dependency Injection**: Services injected via constructor
3. **Database Transactions**: Critical operations wrapped in transactions
4. **Pessimistic Locking**: Row-level locks for booking operations
5. **Eager Loading**: Prevents N+1 query problems
6. **Query Scoping**: Reusable query constraints

---

---

## 🗄️ Database Schema

### Core Tables

#### **Users & Permissions**
```sql
users
├── id (PK)
├── name
├── email (unique)
├── password (hashed)
├── terminal_id (FK) ──> terminals.id (for employees)
├── two_factor_secret
├── two_factor_recovery_codes
├── two_factor_confirmed_at
└── timestamps

roles (Spatie Permission)
├── id (PK)
├── name (unique)
└── guard_name

permissions (Spatie Permission)
├── id (PK)
├── name (unique)
├── module (for grouping)
└── guard_name

model_has_roles (Spatie Permission)
└── role_id, model_id, model_type

model_has_permissions (Spatie Permission)
└── permission_id, model_id, model_type
```

#### **Geographic Structure**
```sql
cities
├── id (PK)
├── name
├── code (unique)
└── status

terminals
├── id (PK)
├── name
├── code (unique)
├── city_id (FK) ──> cities.id
├── address
├── phone
├── status
└── timestamps
```

#### **Route Management**
```sql
routes
├── id (PK)
├── operator_id (FK) ──> users.id
├── code (unique)
├── name
├── direction
├── is_return_of (FK) ──> routes.id (for return routes)
├── base_currency
└── status

route_stops
├── id (PK)
├── route_id (FK) ──> routes.id
├── terminal_id (FK) ──> terminals.id
├── sequence (order along route)
└── timestamps

route_user (many-to-many)
├── route_id (FK) ──> routes.id
└── user_id (FK) ──> users.id
```

#### **Timetable Management**
```sql
timetables
├── id (PK)
├── route_id (FK) ──> routes.id
├── name
├── frequency_type (daily, weekly, etc.)
├── status
└── timestamps

timetable_stops
├── id (PK)
├── timetable_id (FK) ──> timetables.id
├── terminal_id (FK) ──> terminals.id
├── sequence (order along route)
├── departure_time (time)
├── arrival_time (time)
├── is_active
└── timestamps
```

#### **Trip Management**
```sql
trips
├── id (PK)
├── timetable_id (FK) ──> timetables.id
├── route_id (FK) ──> routes.id
├── bus_id (FK) ──> buses.id
├── departure_date (date)
├── departure_datetime (datetime)
├── estimated_arrival_datetime (datetime)
├── driver_name
├── driver_phone
├── driver_license
├── driver_cnic
├── driver_address
├── status
├── notes
├── deleted_at (soft delete)
└── timestamps

trip_stops
├── id (PK)
├── trip_id (FK) ──> trips.id
├── terminal_id (FK) ──> terminals.id
├── sequence (order along route)
├── arrival_at (datetime)
├── departure_at (datetime)
├── is_active
├── is_origin (boolean)
├── is_destination (boolean)
└── timestamps
```

#### **Booking System**
```sql
bookings
├── id (PK)
├── booking_number (unique, 6-digit padded)
├── trip_id (FK) ──> trips.id
├── created_by_type (admin, employee, customer)
├── user_id (FK) ──> users.id (customer/booking owner)
├── booked_by_user_id (FK) ──> users.id (who created booking)
├── terminal_id (FK) ──> terminals.id (source terminal)
├── from_stop_id (FK) ──> route_stops.id (RouteStop ID)
├── to_stop_id (FK) ──> route_stops.id (RouteStop ID)
├── channel (counter, phone, online)
├── status (hold, confirmed, checked_in, boarded, cancelled)
├── reserved_until (datetime, for hold bookings)
├── payment_status (unpaid, paid, refunded)
├── payment_method (cash, card, gateway)
├── online_transaction_id
├── total_fare (decimal 10,2)
├── discount_amount (decimal 10,2)
├── tax_amount (decimal 10,2)
├── final_amount (decimal 10,2)
├── currency (3-char code)
├── total_passengers (integer)
├── notes (text)
├── payment_received_from_customer (decimal 10,2)
├── return_after_deduction_from_customer (decimal 10,2)
├── confirmed_at (datetime)
├── cancelled_at (datetime)
├── deleted_at (soft delete)
└── timestamps

booking_seats
├── id (PK)
├── booking_id (FK) ──> bookings.id
├── seat_number (1-44, integer)
├── from_stop_id (FK) ──> route_stops.id (RouteStop ID)
├── to_stop_id (FK) ──> route_stops.id (RouteStop ID)
├── gender (male, female, null)
└── timestamps

booking_passengers
├── id (PK)
├── booking_id (FK) ──> bookings.id
├── name
├── gender (male, female)
├── phone (nullable)
├── cnic (nullable)
└── timestamps
```

#### **Bus Management**
```sql
bus_types
├── id (PK)
├── name
├── code (unique)
└── description

bus_layouts
├── id (PK)
├── name
├── code (unique)
├── seat_count (integer, default 44)
├── layout_data (JSON)
└── timestamps

buses
├── id (PK)
├── name
├── description
├── bus_type_id (FK) ──> bus_types.id
├── bus_layout_id (FK) ──> bus_layouts.id
├── registration_number (unique)
├── model
├── color
├── status (active, inactive, maintenance)
└── timestamps

bus_facility (many-to-many)
├── bus_id (FK) ──> buses.id
└── facility_id (FK) ──> facilities.id

bus_assignments
├── id (PK)
├── trip_id (FK) ──> trips.id
├── from_trip_stop_id (FK) ──> trip_stops.id
├── to_trip_stop_id (FK) ──> trip_stops.id
├── bus_id (FK) ──> buses.id
├── segment_label (e.g., "Segment 1-3")
├── driver_name
├── driver_phone
├── host_name
├── host_phone
├── assigned_by_user_id (FK) ──> users.id
└── timestamps
```

#### **Fare Management**
```sql
fares
├── id (PK)
├── from_terminal_id (FK) ──> terminals.id
├── to_terminal_id (FK) ──> terminals.id
├── base_fare (decimal 10,2)
├── discount_amount (decimal 10,2)
├── tax_amount (decimal 10,2)
├── final_fare (decimal 10,2)
├── currency (3-char code)
├── status
└── timestamps
```

#### **Other Tables**
```sql
discounts
├── id (PK)
├── route_id (FK) ──> routes.id
├── type (percentage, fixed)
├── value (decimal)
├── starts_at (date)
├── starts_time (time)
├── ends_at (date)
├── ends_time (time)
└── status

announcements
├── id (PK)
├── title
├── content
├── audience_type (all, specific_users, terminals)
├── display_type (banner, popup, notification)
├── priority (low, medium, high)
├── starts_at
├── ends_at
└── status

enquiries
├── id (PK)
├── name
├── email
├── phone
├── subject
├── message
├── status
└── timestamps

expenses
├── id (PK)
├── trip_id (FK) ──> trips.id
├── type (fuel, toll, food, other)
├── amount (decimal 10,2)
├── description
├── expense_date
└── timestamps
```

### Key Relationships

```
User
├── belongsTo Terminal (for employees)
├── belongsToMany Routes (via route_user)
├── hasMany Bookings (as customer)
└── hasMany Bookings (as booker via booked_by_user_id)

Trip
├── belongsTo Timetable
├── belongsTo Route
├── belongsTo Bus
├── hasMany TripStops
├── hasMany Bookings
└── hasMany BusAssignments

Booking
├── belongsTo Trip
├── belongsTo User (customer)
├── belongsTo User (booker)
├── belongsTo Terminal (source)
├── belongsTo RouteStop (from_stop_id)
├── belongsTo RouteStop (to_stop_id)
├── hasMany BookingSeats
└── hasMany BookingPassengers

Route
├── belongsTo User (operator)
├── belongsToMany Users (via route_user)
├── hasMany RouteStops
├── hasMany Timetables
├── hasMany Trips
└── hasMany Fares

RouteStop
├── belongsTo Route
└── belongsTo Terminal

TripStop
├── belongsTo Trip
└── belongsTo Terminal

BusAssignment
├── belongsTo Trip
├── belongsTo Bus
├── belongsTo TripStop (from_trip_stop_id)
├── belongsTo TripStop (to_trip_stop_id)
└── belongsTo User (assigned_by)
```

### Important Design Decisions

1. **RouteStop vs TripStop**:
   - `RouteStop`: Defines the route structure (static, reusable)
   - `TripStop`: Actual stop instance for a specific trip (dynamic, date-specific)
   - Bookings store `RouteStop` IDs for consistency across trips

2. **Booking Storage**:
   - `from_stop_id` and `to_stop_id` reference `route_stops` table
   - This ensures bookings are tied to route structure, not specific trip instances
   - Allows for consistent fare calculation and route visualization

3. **Multi-Segment Support**:
   - `BusAssignment` allows different buses for different segments of the same trip
   - Example: Bus A handles stops 1-3, Bus B handles stops 3-5

4. **Soft Deletes**:
   - `trips`, `bookings`, and related models use soft deletes
   - Preserves data integrity while allowing "deletion"

---

---

## 🎫 Booking System & Complete Flow

This section explains the entire booking process from start to finish, including all algorithms and data retrieval steps in simple, understandable terms.

### 📍 Step 1: Getting Available Terminals

**What happens**: When a user opens the booking console, the system loads all active terminals they can book from.

**Algorithm**:
```
1. Check if user is an employee:
   - IF user has terminal_id assigned:
     → Show ONLY that terminal (user is locked to their terminal)
   - ELSE (user is admin):
     → Show ALL active terminals

2. Query terminals table:
   - WHERE status = 'active'
   - IF employee: WHERE id = user.terminal_id
   - ORDER BY name

3. Return list: [id, name, code, city_id]
```

**Example**:
- Admin sees: Karachi Terminal, Lahore Terminal, Islamabad Terminal
- Employee (assigned to Karachi) sees: Karachi Terminal only

---

### 🛣️ Step 2: Getting Routes for Selected Terminal

**What happens**: Once a terminal is selected, find all routes that pass through that terminal.

**Algorithm**:
```
1. User selects a terminal (e.g., "Karachi Terminal" - ID: 5)

2. Find all routes that include this terminal:
   - Query: routes table
   - JOIN with route_stops table
   - WHERE route_stops.terminal_id = 5 (selected terminal)
   - AND routes.status = 'active'

3. Filter by user restrictions (if employee):
   - IF user has specific routes assigned:
     → Show ONLY those routes
   - ELSE:
     → Show all routes containing the terminal

4. Return routes: [id, name, code, direction, base_currency]
```

**Example**:
- Routes containing "Karachi Terminal":
  - Route A: Karachi → Lahore → Islamabad
  - Route B: Karachi → Multan
  - Route C: Karachi → Quetta

---

### 🚏 Step 3: Getting Forward Stops (To Terminal Selection)

**What happens**: After selecting "From Terminal", show only destinations that come AFTER it on the route (forward movement only).

**Algorithm**:
```
1. User selected "From Terminal" = Karachi (Terminal ID: 5)

2. For each route that contains Karachi:
   
   a. Get ALL stops for that route:
      - Query route_stops WHERE route_id = X
      - ORDER BY sequence (1, 2, 3, 4...)
   
   b. Find Karachi's position:
      - Find route_stop where terminal_id = 5
      - Get its sequence number (e.g., sequence = 1)
   
   c. Filter forward stops only:
      - Get all stops WHERE sequence > 1
      - Example: 
        * Sequence 1: Karachi (from) ❌ Skip
        * Sequence 2: Lahore ✅ Include
        * Sequence 3: Islamabad ✅ Include
        * Sequence 4: Peshawar ✅ Include

3. Combine stops from all routes

4. Remove duplicates (same terminal appears in multiple routes)

5. Return: [terminal_id, name, code, sequence]
```

**Visual Example**:
```
Route: Karachi → Lahore → Islamabad → Peshawar
Sequence:  1        2         3         4

Selected From: Karachi (sequence 1)
Available To:  Lahore, Islamabad, Peshawar
```

**Why Forward Only?**
- Prevents booking backwards (Lahore → Karachi when bus is going Karachi → Lahore)
- Ensures logical travel direction

---

### ⏰ Step 4: Getting Departure Times

**What happens**: Find all available departure times for the selected date and route segment.

**Algorithm**:
```
1. Inputs:
   - from_terminal_id: 5 (Karachi)
   - to_terminal_id: 10 (Lahore)
   - date: 2025-12-15

2. Validate:
   - Both terminals must be different
   - Date must be today or future
   - Check current time (don't show past times)

3. Find routes containing both terminals:
   - Get route_stops for all routes
   - Find routes where:
     * Contains terminal 5 (Karachi)
     * Contains terminal 10 (Lahore)
     * Karachi.sequence < Lahore.sequence (forward direction)

4. Get timetable stops:
   - Query timetable_stops table
   - WHERE terminal_id = 5 (from terminal)
   - AND is_active = true
   - Load related timetable → route

5. Filter valid departures:
   - For each timetable_stop:
     a. Combine date + departure_time = full datetime
        Example: "2025-12-15" + "08:00:00" = "2025-12-15 08:00:00"
     
     b. Check if future:
        IF full_datetime >= current_time:
           ✅ Include this departure
        ELSE:
           ❌ Skip (already departed)

6. Sort by departure time (earliest first)

7. Return: [timetable_stop_id, departure_time, arrival_time, route_id, route_name]
```

**Example Result**:
```json
[
  {
    "id": 101,
    "departure_at": "08:00:00",
    "arrival_at": "14:30:00",
    "route_name": "Karachi → Lahore"
  },
  {
    "id": 102,
    "departure_at": "14:00:00",
    "arrival_at": "20:30:00",
    "route_name": "Karachi → Lahore"
  }
]
```

---

### 🚌 Step 5: Loading/Creating Trip

**What happens**: When user clicks "Load Trip", the system finds or creates a trip for the selected timetable and date, then maps route stops to trip stops.

**Algorithm**:
```
1. Inputs:
   - timetable_id: 50
   - date: 2025-12-15
   - from_terminal_id: 5
   - to_terminal_id: 10

2. Get timetable:
   - Load timetable with route and timetable_stops
   - Verify timetable exists and is active

3. Check if trip already exists:
   - Query trips table:
     WHERE timetable_id = 50
     AND departure_date = '2025-12-15'
   
   IF trip exists:
     → Use existing trip
   ELSE:
     → Create new trip (see Step 5b)

4. Map route stops to trip stops:
   a. Get route_stops for the route:
      - Query route_stops WHERE route_id = route.id
      - ORDER BY sequence
   
   b. Get trip_stops for the trip:
      - Query trip_stops WHERE trip_id = trip.id
      - ORDER BY sequence
   
   c. Match by terminal_id and sequence:
      - For route_stop with terminal_id=5, sequence=1:
        → Find trip_stop with terminal_id=5, sequence=1
      - Store both RouteStop ID and TripStop ID

5. Validate segment:
   - from_route_stop.sequence < to_route_stop.sequence ✅
   - Both stops exist in trip ✅

6. Return:
   - trip (with bus info)
   - route (id, name, code)
   - route_stops (all stops for display)
   - from_stop (with both route_stop_id and trip_stop_id)
   - to_stop (with both route_stop_id and trip_stop_id)
```

**Step 5b: Creating New Trip (Auto-Trip Creation)**:
```
IF trip doesn't exist:

1. Create trip record:
   - timetable_id: 50
   - route_id: (from timetable)
   - departure_date: '2025-12-15'
   - status: 'scheduled'

2. Create trip_stops from timetable_stops:
   FOR each timetable_stop in timetable:
     a. Combine date + departure_time → departure_at
     b. Combine date + arrival_time → arrival_at
     c. Create trip_stop:
        - trip_id: new trip id
        - terminal_id: from timetable_stop
        - sequence: from timetable_stop
        - arrival_at: calculated datetime
        - departure_at: calculated datetime
        - is_origin: true (if first stop)
        - is_destination: true (if last stop)

3. Update trip:
   - departure_datetime: first stop's departure_at
   - estimated_arrival_datetime: last stop's arrival_at

4. Return created trip with stops loaded
```

**Important**: 
- RouteStop = Template (defines route structure)
- TripStop = Actual instance (specific date/time)
- Bookings store RouteStop IDs for consistency

---

### 💺 Step 6: Seat Availability Check (The Core Algorithm)

**What happens**: Determine which seats are available for the selected route segment, preventing double-booking for overlapping segments.

**The Problem**: 
- If someone books Seat 5 from Stop 1→3
- And someone else wants Seat 5 from Stop 2→4
- These overlap! (both occupy seat between stops 2-3)
- Only ONE person can have the seat for overlapping segments

**Algorithm - Segment Overlap Detection**:
```
1. Inputs:
   - trip_id: 100
   - from_trip_stop_id: 501 (TripStop ID)
   - to_trip_stop_id: 503 (TripStop ID)

2. Get query segment sequences:
   - Load trip_stops for trip
   - Get sequence numbers:
     from_seq = trip_stops[501].sequence  → e.g., 1
     to_seq = trip_stops[503].sequence    → e.g., 3
   
   Query segment: [1 → 3]

3. Get RouteStop map:
   - Load all route_stops for this route
   - Create map: route_stop_id → sequence
   - Example: {201: 1, 202: 2, 203: 3, 204: 4}

4. Get all active bookings for this trip:
   - Query bookings WHERE trip_id = 100
   - Status IN: ['confirmed', 'checked_in', 'boarded', 'hold']
   - For 'hold': Only if reserved_until > now()
   - Load relationships: fromStop, toStop, seats

5. For each booking, check overlap:
   
   FOR each booking:
     a. Get booking segment:
        booking_from_seq = booking.fromStop.sequence  (RouteStop sequence)
        booking_to_seq = booking.toStop.sequence      (RouteStop sequence)
        
        Booking segment: [booking_from_seq → booking_to_seq]
     
     b. Overlap check (mathematical):
        IF booking_from_seq < query_to_seq 
        AND query_from_seq < booking_to_seq:
           ✅ OVERLAP DETECTED
        ELSE:
           ❌ No overlap
     
     c. IF overlap:
        Mark all seats in this booking as OCCUPIED

6. Example Overlap Scenarios:
   
   Scenario 1: OVERLAP ✅
   Query:      [2 → 4]
   Booking:    [1 → 3]
   Check:      1 < 4 (true) AND 2 < 3 (true) → OVERLAP
   
   Scenario 2: OVERLAP ✅
   Query:      [1 → 3]
   Booking:    [2 → 4]
   Check:      2 < 3 (true) AND 1 < 4 (true) → OVERLAP
   
   Scenario 3: NO OVERLAP ❌
   Query:      [1 → 2]
   Booking:    [3 → 4]
   Check:      3 < 2 (false) → NO OVERLAP
   
   Scenario 4: NO OVERLAP ❌
   Query:      [3 → 4]
   Booking:    [1 → 2]
   Check:      1 < 4 (true) BUT 3 < 2 (false) → NO OVERLAP

7. Build occupancy map:
   Initialize: occupancy[seat_number] = []
   
   FOR each booking with overlap:
     FOR each seat in booking:
       occupancy[seat.seat_number].push([booking_from_seq, booking_to_seq])

8. Find available seats:
   FOR seat_number from 1 to 44:
     hit = false
     
     FOR each overlap in occupancy[seat_number]:
       IF overlap overlaps with query segment:
         hit = true
         break
     
     IF not hit:
       ✅ Seat is AVAILABLE
       Add to available_seats array
     ELSE:
       ❌ Seat is OCCUPIED

9. Return: [5, 7, 12, 15, ...] (list of available seat numbers)
```

**Visual Example**:
```
Trip Route: Karachi(1) → Lahore(2) → Islamabad(3) → Peshawar(4)

Existing Bookings:
- Booking A: Seat 5, Segment [1 → 2] (Karachi → Lahore)
- Booking B: Seat 10, Segment [2 → 3] (Lahore → Islamabad)
- Booking C: Seat 5, Segment [3 → 4] (Islamabad → Peshawar)

User Query: Segment [1 → 3] (Karachi → Islamabad)

Overlap Check:
- Booking A: [1 → 2] vs Query [1 → 3]
  → 1 < 3 ✅ AND 1 < 2 ✅ → OVERLAP
  → Seat 5 = OCCUPIED
  
- Booking B: [2 → 3] vs Query [1 → 3]
  → 2 < 3 ✅ AND 1 < 3 ✅ → OVERLAP
  → Seat 10 = OCCUPIED
  
- Booking C: [3 → 4] vs Query [1 → 3]
  → 3 < 3 ❌ → NO OVERLAP
  → Seat 5 still shows as occupied (from Booking A)

Result: Seat 5 = Occupied, Seat 10 = Occupied, Other seats = Available
```

---

### ✅ Step 7: Creating a Booking

**What happens**: When user confirms booking, the system validates, locks seats, creates booking record, and processes payment.

**Algorithm**:
```
1. Validate Input:
   - trip_id: exists
   - from_terminal_id: exists
   - to_terminal_id: exists, different from from_terminal_id
   - seat_numbers: array, 1-44, at least 1 seat
   - passengers: array, count matches seat_numbers
   - channel: 'counter' | 'phone' | 'online'
   - total_fare, discount_amount, tax_amount, final_amount
   - IF counter: payment_method, amount_received

2. Start Database Transaction (atomic operation):
   BEGIN TRANSACTION

3. Lock Trip Row (prevent race conditions):
   - SELECT * FROM trips WHERE id = X FOR UPDATE
   - This locks the row so no other booking can interfere

4. Map RouteStop IDs:
   a. Get trip → route
   b. Get route_stops for route
   c. Find from_route_stop:
      WHERE route_id = route.id
      AND terminal_id = from_terminal_id
      AND sequence = (from_trip_stop.sequence)
   
   d. Find to_route_stop:
      WHERE route_id = route.id
      AND terminal_id = to_terminal_id
      AND sequence = (to_trip_stop.sequence)

5. Re-check Availability (inside lock):
   - Call AvailabilityService.availableSeats()
   - Verify all requested seats are still available
   - IF any seat unavailable: ROLLBACK, throw error

6. Validate Segment:
   - from_trip_stop.sequence < to_trip_stop.sequence ✅
   - Departure hasn't passed ✅
   - Segment is valid ✅

7. Determine Booking Status:
   IF channel == 'counter':
     status = 'confirmed'
     payment_status = 'paid'
     payment_method = 'cash' | 'card'
   ELSE IF channel == 'phone':
     status = 'hold'
     payment_status = 'unpaid'
     reserved_until = departure_time - 15 minutes
   ELSE (online):
     status = 'hold'
     payment_status = 'unpaid'

8. Generate Booking Number:
   - Get last booking number
   - Increment by 1
   - Pad to 6 digits: "000123"

9. Create Booking Record:
   INSERT INTO bookings:
   - booking_number: "000123"
   - trip_id: 100
   - from_stop_id: from_route_stop.id  ← RouteStop ID
   - to_stop_id: to_route_stop.id      ← RouteStop ID
   - user_id: customer.id (if customer booking)
   - booked_by_user_id: current_user.id
   - terminal_id: source terminal
   - channel: 'counter' | 'phone' | 'online'
   - status: calculated above
   - payment_status: calculated above
   - total_fare, discount_amount, tax_amount, final_amount
   - payment_received_from_customer (counter only)
   - return_after_deduction_from_customer (counter only)

10. Create BookingSeat Records:
    FOR each seat_number in seat_numbers:
      INSERT INTO booking_seats:
      - booking_id: new booking.id
      - seat_number: 5, 6, 7...
      - from_stop_id: from_route_stop.id  ← RouteStop ID
      - to_stop_id: to_route_stop.id      ← RouteStop ID
      - gender: 'male' | 'female' | null

11. Create BookingPassenger Records:
    FOR each passenger in passengers:
      INSERT INTO booking_passengers:
      - booking_id: new booking.id
      - name: passenger.name
      - gender: passenger.gender
      - phone: passenger.phone (optional)
      - cnic: passenger.cnic (optional)

12. Broadcast Events:
    - SeatConfirmed::dispatch(trip_id, seat_numbers, user)
    - This updates all other users' seat maps in real-time

13. COMMIT TRANSACTION
    - All or nothing: If any step fails, rollback everything

14. Return Success Response:
    {
      "booking": {
        "id": 500,
        "booking_number": "000123",
        "status": "confirmed",
        "seats": [5, 6, 7],
        "final_amount": "3500.00"
      }
    }
```

**Important Points**:
- **RouteStop IDs are stored** in bookings (not TripStop IDs)
- This allows bookings to reference route structure, not specific trip instances
- Works even if trip is deleted/recreated
- Enables accurate fare calculation and route visualization

---

### 🔄 Complete Booking Flow Summary

```
USER ACTION                    SYSTEM PROCESS
─────────────────────────────────────────────────────────
1. Open Console          →    Load Terminals
                             (Employee: Only their terminal)
                             (Admin: All terminals)

2. Select From Terminal  →    Load Routes
                             (Routes containing that terminal)

3. Routes Loaded        →    Load Forward Stops
                             (Stops after selected terminal)

4. Select To Terminal   →    Load Departure Times
                             (For selected date and segment)

5. Select Departure     →    Load/Create Trip
   Click "Load Trip"         (Find or auto-create trip)
                             (Map RouteStops to TripStops)

6. Trip Loaded          →    Calculate Availability
                             (Check segment overlaps)
                             (Build seat map)

7. Select Seats         →    Lock Seats (WebSocket)
                             (Broadcast SeatLocked event)

8. Fill Booking Info    →    Validate Payment
                             (Calculate return amount)

9. Click "Confirm"      →    Create Booking
                             (Transaction + Lock)
                             (Create records)
                             (Broadcast SeatConfirmed)
```

---

### 🎯 Key Design Decisions Explained

1. **Why RouteStop IDs in Bookings?**
   - RouteStop = Route structure (permanent)
   - TripStop = Specific trip instance (date-dependent)
   - Storing RouteStop IDs means bookings reference route structure
   - Works even if trip is recreated for same route

2. **Why Segment Overlap Check?**
   - Prevents double-booking same seat for overlapping segments
   - Example: Seat 5 booked Karachi→Lahore, can't book Lahore→Islamabad
   - Mathematical overlap: `booking_from < query_to AND query_from < booking_to`

3. **Why Pessimistic Locking?**
   - `lockForUpdate()` prevents race conditions
   - Two users booking same seat simultaneously: only one succeeds
   - Lock held for entire transaction duration

4. **Why Auto-Trip Creation?**
   - Timetables define schedules
   - Trips are date-specific instances
   - Auto-creating saves manual work
   - Ensures trip exists when needed

---

*Next sections: Permission System, Services Details, API Documentation*

---

**Last Updated**: December 2025  
**Version**: 1.0.0  
**Laravel**: 12.33.0  
**PHP**: 8.2.12
