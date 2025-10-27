# 🎫 Booking System Documentation

## Overview

The Booking System allows employees to create bookings for customers through a comprehensive multi-step flow. This system supports counter bookings, phone bookings, and handles seat selection, passenger details, and payment processing.

---

## 📋 Table of Contents

1. [Features](#features)
2. [Database Structure](#database-structure)
3. [Booking Flow](#booking-flow)
4. [User Interface](#user-interface)
5. [API Endpoints](#api-endpoints)
6. [Code Examples](#code-examples)
7. [Testing](#testing)

---

## ✨ Features

### Core Features
- **Multi-step booking creation** with validation at each step
- **45-seat bus layout** with real-time availability checking
- **Segment-based booking** (from any stop to any stop on a route)
- **Automatic trip creation** if no trip exists for the selected date/route
- **Passenger detail collection** (Name, CNIC, Gender, Age, Phone)
- **Multiple booking types**: Counter, Phone
- **Payment tracking**: Cash, Card, Mobile Wallet, Bank Transfer
- **Reserved seats for phone bookings** (auto-release 30 mins before departure)
- **Discount support** for promotional offers
- **Booking cancellation** with reason tracking

### Seat Management
- Visual seat selection interface
- Real-time seat availability checking
- Overlapping segment detection
- Prevents double-booking of seats

### Trip Management
- Auto-creates trips with status "planned" when no trip exists
- Bus assignment happens later by admin
- Trip status: `planned` → `active` → `completed`

---

## 🗄️ Database Structure

### Tables

#### 1. `bookings` Table
```sql
- id (PK)
- booking_number (unique, auto-generated)
- trip_id (FK → trips)
- user_id (FK → users, nullable for counter/phone)
- booked_by_user_id (FK → users, the employee)
- from_stop_id (FK → route_stops)
- to_stop_id (FK → route_stops)
- type (enum: online, counter, phone)
- status (enum: pending, confirmed, cancelled, completed)
- total_fare (decimal)
- discount_amount (decimal)
- final_amount (decimal)
- currency (varchar)
- total_passengers (int)
- passenger_contact_name (text)
- passenger_contact_phone (text)
- passenger_contact_email (text, nullable)
- notes (text, nullable)
- metadata (json)
- confirmed_at (timestamp, nullable)
- cancelled_at (timestamp, nullable)
- reserved_until (timestamp, nullable) [NEW]
- payment_status (enum: pending, partial, paid, refunded, failed) [NEW]
- payment_method (enum: cash, card, mobile_wallet, bank_transfer, other) [NEW]
- created_at, updated_at, deleted_at
```

#### 2. `booking_seats` Table
```sql
- id (PK)
- booking_id (FK → bookings)
- seat_number (varchar)
- seat_row (varchar)
- seat_column (varchar)
- passenger_name (varchar)
- passenger_age (varchar, nullable)
- passenger_gender (enum: male, female, other)
- passenger_cnic (varchar)
- passenger_phone (varchar, nullable)
- fare (decimal)
- notes (text, nullable)
- created_at, updated_at
```

#### 3. `trips` Table
```sql
- id (PK)
- timetable_id (FK, nullable)
- route_id (FK → routes)
- bus_id (FK → buses, nullable)
- departure_date (date)
- departure_datetime (timestamp)
- estimated_arrival_datetime (timestamp)
- status (enum: planned, scheduled, in_progress, completed, cancelled)
- notes (text, nullable)
- created_at, updated_at, deleted_at
```

### Relationships

```
Route (1) ──┬→ (many) RouteStops
            └→ (many) Trips

Trip (1) ───→ (many) Bookings

Booking (1) ─→ (many) BookingSeats

RouteStop ←── from_stop_id ─┐
RouteStop ←── to_stop_id ────┤─→ Booking
User ←── booked_by_user_id ──┘
```

---

## 🔄 Booking Flow

### Step 1: Search for Available Seats
**Route**: `/admin/bookings/create`

Employee selects:
- Route
- Departure Date
- Departure Time
- From Terminal
- To Terminal

**System Actions**:
1. Validates terminal sequence (to_stop must come after from_stop)
2. Creates or fetches existing trip for route + date
3. Calculates fare for the segment
4. Fetches booked seats for overlapping segments
5. Redirects to seat selection page

### Step 2: Select Seats
**Route**: `/admin/bookings/search` (POST)

**Display**:
- 45-seat bus layout (11 rows × 4 seats + 1 last row with 5 seats)
- Driver seat (non-selectable)
- Available seats (green)
- Booked seats (red, non-selectable)
- Selected seats (blue)

**Features**:
- Click to select/deselect seats
- Real-time fare calculation
- Shows trip information

**System Actions**:
1. Validates seat availability
2. Checks for seat conflicts
3. Passes selected seats to passenger details form

### Step 3: Enter Passenger Details
**Route**: `/admin/bookings/select-seats` (POST)

**For Each Passenger**:
- Full Name (required)
- CNIC (required, format: XXXXX-XXXXXXX-X)
- Gender (required)
- Age (optional)
- Phone (optional)

**Contact Person**:
- Contact Name (required)
- Contact Phone (required)
- Contact Email (optional)
- Notes (optional)

**Booking Details**:
- Booking Type (counter/phone)
- Payment Method (cash/card/mobile_wallet/bank_transfer/other)
- Payment Status (paid/pending)
- Discount Amount (optional)

### Step 4: Confirm Booking
**Route**: `/admin/bookings/store` (POST)

**System Actions**:
1. Validates all passenger data
2. Re-checks seat availability (prevent race conditions)
3. Calculates final amount (total_fare - discount)
4. Creates `Booking` record
5. Creates `BookingSeat` records for each passenger
6. Sets `confirmed_at` if payment_status = 'paid'
7. Sets `reserved_until` for phone bookings (departure - 30 mins)
8. Generates unique `booking_number`
9. Redirects to booking details page

---

## 🎨 User Interface

### 1. Search Form (`create.blade.php`)
- Clean, centered form with gradient header
- Select2 dropdowns for routes and terminals
- Date picker with minimum date validation
- Time picker for departure time
- Info box with helpful tips

### 2. Seat Selection (`select-seats.blade.php`)
- Visual bus layout with 45 seats
- Color-coded seats:
  - Green border: Available
  - Blue background: Selected
  - Red background: Booked
  - Gray: Driver seat
- Real-time fare summary sidebar
- Trip information card
- Legend for seat statuses

### 3. Passenger Details (`passenger-details.blade.php`)
- One card per passenger with seat number badge
- Auto-formatting for CNIC (XXXXX-XXXXXXX-X)
- Auto-formatting for phone (03XX-XXXXXXX)
- Contact person section
- Sticky fare summary with booking options
- Payment method and status selectors

### 4. Booking Details (`show.blade.php`)
- Comprehensive booking information
- Trip details with route and departure info
- Passenger table with all details
- Payment summary
- Cancel booking option (if eligible)

---

## 🔌 API Endpoints

### Public Routes
```php
// None - all booking creation is admin-only
```

### Admin Routes (Requires Authentication)
```php
// Booking Creation Flow
GET    /admin/bookings/create              // Show search form
POST   /admin/bookings/search              // Search seats
POST   /admin/bookings/select-seats        // Process seat selection
POST   /admin/bookings/store               // Create booking

// Booking Management
GET    /admin/bookings                     // List all bookings
GET    /admin/bookings/{id}                // View booking details
POST   /admin/bookings/{id}/cancel-booking // Cancel booking
```

---

## 💻 Code Examples

### 1. Calculate Segment Fare

```php
protected function calculateSegmentFare(RouteStop $fromStop, RouteStop $toStop, Route $route): float
{
    $totalFare = 0;

    // Get all stops between from and to
    $stops = $route->routeStops()
        ->whereBetween('sequence', [$fromStop->sequence, $toStop->sequence])
        ->orderBy('sequence')
        ->get();

    // Sum up fares for consecutive stop pairs
    for ($i = 0; $i < count($stops) - 1; $i++) {
        $fare = Fare::where('from_terminal_id', $stops[$i]->terminal_id)
            ->where('to_terminal_id', $stops[$i + 1]->terminal_id)
            ->value('final_fare');

        $totalFare += $fare ?? 0;
    }

    return $totalFare;
}
```

### 2. Get Booked Seats for Segment

```php
protected function getBookedSeatsForSegment(int $tripId, int $fromStopId, int $toStopId): array
{
    $fromStop = RouteStop::findOrFail($fromStopId);
    $toStop = RouteStop::findOrFail($toStopId);

    $bookings = Booking::where('trip_id', $tripId)
        ->whereIn('status', [BookingStatusEnum::Pending, BookingStatusEnum::Confirmed])
        ->with(['fromStop', 'toStop', 'bookingSeats'])
        ->get();

    $bookedSeats = [];

    foreach ($bookings as $booking) {
        // Check if segments overlap
        // Overlap occurs if: (booking_from < search_to) AND (booking_to > search_from)
        if ($booking->fromStop->sequence < $toStop->sequence &&
            $booking->toStop->sequence > $fromStop->sequence) {
            $bookedSeats = array_merge(
                $bookedSeats,
                $booking->bookingSeats->pluck('seat_number')->toArray()
            );
        }
    }

    return array_unique($bookedSeats);
}
```

### 3. Auto-Create Trip

```php
$trip = Trip::firstOrCreate(
    [
        'route_id' => $validated['route_id'],
        'departure_date' => $validated['departure_date'],
    ],
    [
        'departure_datetime' => $departureDateTime,
        'estimated_arrival_datetime' => date('Y-m-d H:i:s', strtotime($departureDateTime.' +4 hours')),
        'status' => 'planned',
        'bus_id' => null, // Bus will be assigned later by admin
    ]
);
```

### 4. Create Booking with Transaction

```php
DB::transaction(function () use ($validated) {
    $booking = Booking::create([
        'trip_id' => $validated['trip_id'],
        'booked_by_user_id' => auth()->id(),
        'from_stop_id' => $validated['from_stop_id'],
        'to_stop_id' => $validated['to_stop_id'],
        'type' => $validated['booking_type'],
        'status' => $validated['payment_status'] === 'paid' 
            ? BookingStatusEnum::Confirmed 
            : BookingStatusEnum::Pending,
        'total_fare' => $validated['total_fare'],
        'final_amount' => $validated['total_fare'] - $discountAmount,
        'payment_status' => $validated['payment_status'],
        'payment_method' => $validated['payment_method'],
        // ... other fields
    ]);

    foreach ($validated['seats'] as $seatData) {
        BookingSeat::create([
            'booking_id' => $booking->id,
            'seat_number' => $seatData['seat_number'],
            'passenger_name' => $seatData['passenger_name'],
            // ... other fields
        ]);
    }
});
```

---

## 🧪 Testing

### Manual Testing Checklist

#### ✅ Step 1: Search
- [ ] Can select a route
- [ ] Can select from/to terminals
- [ ] Can set future departure date
- [ ] Validates that to_terminal comes after from_terminal
- [ ] Creates new trip if none exists
- [ ] Fetches existing trip if it exists

#### ✅ Step 2: Seat Selection
- [ ] Shows 45 seats in correct layout
- [ ] Booked seats are marked red and non-clickable
- [ ] Available seats are clickable
- [ ] Selected seats turn blue
- [ ] Can deselect seats
- [ ] Fare summary updates in real-time
- [ ] Continue button disabled when no seats selected
- [ ] Continue button enabled when seats selected

#### ✅ Step 3: Passenger Details
- [ ] One form per selected seat
- [ ] All required fields validated
- [ ] CNIC auto-formats correctly
- [ ] Phone auto-formats correctly
- [ ] Contact person details required
- [ ] Can select booking type
- [ ] Can select payment method and status
- [ ] Can enter discount amount
- [ ] Final amount calculates correctly

#### ✅ Step 4: Confirmation
- [ ] Booking created successfully
- [ ] Unique booking number generated
- [ ] All passenger details saved
- [ ] Payment details saved correctly
- [ ] Seats marked as booked
- [ ] Can view booking details
- [ ] Can cancel eligible bookings

### Automated Testing (Future)

```php
/** @test */
public function it_creates_a_booking_with_passengers()
{
    $route = Route::factory()->create();
    $fromStop = RouteStop::factory()->create(['route_id' => $route->id, 'sequence' => 1]);
    $toStop = RouteStop::factory()->create(['route_id' => $route->id, 'sequence' => 3]);
    
    $response = $this->post(route('admin.bookings.store'), [
        'trip_id' => $trip->id,
        'from_stop_id' => $fromStop->id,
        'to_stop_id' => $toStop->id,
        'booking_type' => 'counter',
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        // ... other fields
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('bookings', [
        'trip_id' => $trip->id,
        'from_stop_id' => $fromStop->id,
        'to_stop_id' => $toStop->id,
    ]);
}
```

---

## 🚀 Deployment Notes

### Prerequisites
1. Run migrations:
   ```bash
   php artisan migrate
   ```

2. Ensure these enums exist:
   - `BookingTypeEnum` (online, counter, phone)
   - `BookingStatusEnum` (pending, confirmed, cancelled, completed)

3. Seed initial data:
   ```bash
   php artisan db:seed --class=RouteSeeder
   php artisan db:seed --class=TerminalSeeder
   php artisan db:seed --class=FareSeeder
   ```

### Configuration
No special configuration required. The system uses default Laravel settings.

---

## 📝 Future Enhancements

1. **SMS Notifications**: Send booking confirmation via SMS
2. **Email Receipts**: Email booking receipt to passenger
3. **Seat Locking**: Redis-based seat locking during selection
4. **Auto-release Job**: Cron job to auto-release expired phone bookings
5. **Booking Modification**: Allow changing passenger details
6. **Refund Processing**: Handle booking cancellations with refunds
7. **Multi-language Support**: Support Urdu and English
8. **Mobile App Integration**: API for mobile bookings
9. **QR Code Tickets**: Generate QR codes for tickets
10. **Analytics Dashboard**: Booking statistics and reports

---

## 📞 Support

For issues or questions, please contact the development team.

**Last Updated**: October 27, 2025
**Version**: 1.0
**Author**: Development Team

