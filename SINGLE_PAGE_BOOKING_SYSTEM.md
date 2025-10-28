# Single-Page Booking System with Employee Permissions

## Overview
A comprehensive, enterprise-grade single-page booking system that handles the entire booking flow without page redirects. The system includes employee permission management, terminal tracking, real-time seat selection, and payment calculation.

## Key Features

### 1. **Terminal-Based Booking**
- The system now tracks which terminal the booking is being created from
- The "From Terminal" selection sets the employee's current working terminal
- This is displayed in the employee info badge at the top of the page

### 2. **Employee Route Permissions**
The system implements role-based access control for bookings:

#### **Super Admin & Admin**
- Can create bookings from ANY terminal
- Can book for ALL routes
- No restrictions

#### **Employee**
- Can only create bookings from their ASSIGNED terminals
- Can only book for their ASSIGNED routes
- Unauthorized terminals are disabled and marked in the dropdown
- Attempting to select an unauthorized terminal shows an error

#### **Permission Structure**
Employee permissions are managed through the `employee_routes` table:
- `user_id` - The employee's ID
- `route_id` - The route they're authorized for
- `starting_terminal_id` - The terminal they can book from
- `is_active` - Whether this permission is active

### 3. **Complete Single-Page Flow**

#### **Step 1: Search Trip**
- Select departure terminal (From Terminal)
  - Validates employee permissions
  - Sets the booking terminal
- Select destination terminal
- Choose departure date
- Select number of passengers (1-10)
- Load available times
- Choose specific departure time and route

#### **Step 2: Select Seats**
- Visual bus layout with 45 seats
- Interactive seat selection
- Real-time seat status:
  - **Available** - Green, can be selected
  - **Your Selection** - Purple gradient with gender badge
  - **Locked by Others** - Gray dashed border with lock icon
  - **Booked (Pending)** - Yellow with hourglass icon
  - **Sold (Confirmed)** - Red with checkmark
- Gender selection for each seat (Male/Female)
- Selection summary with live fare calculation
- Validates correct number of seats selected

#### **Step 3: Passenger Details**
- Dynamic passenger forms based on selected seats
- For each passenger:
  - Full Name (required)
  - CNIC (required)
  - Age (optional)
  - Phone (optional)
  - Gender (auto-filled from seat selection)
- Contact person details:
  - Contact Name (required)
  - Contact Phone (required)
  - Contact Email (optional)
  - Booking Notes (optional)
- Real-time validation

#### **Step 4: Payment & Confirmation**
- Booking Type selection (Counter/Phone)
- Payment Method selection (Cash/Card/Mobile Wallet/Bank Transfer)
- Discount input (optional)
- Payment Calculator:
  - Amount Received input
  - Automatic change calculation
  - Insufficient payment warning
- Final Bill Summary:
  - Subtotal
  - Discount
  - Grand Total
  - Amount Received
  - Change
- Payment status automatically determined:
  - **Paid** - If full amount received
  - **Pending** - If partial payment

### 4. **Visual Progress Tracking**
- 4-step wizard with progress bar
- Active step highlighting
- Completed steps marked with checkmark
- Smooth animations between steps
- Can navigate back to previous steps

### 5. **Live Payment Calculator**
The calculator updates in real-time as you enter:
- Base fare (per seat × number of seats)
- Discount amount
- Amount received from customer
- Change to return
- Payment status warning if insufficient

### 6. **Employee Information Display**
Top banner shows:
- Booking agent name and email
- Current terminal (updates when From Terminal is selected)
- User role (Super Admin/Admin/Employee)
- For Employees: List of authorized routes

## Technical Implementation

### Files Created/Modified

#### **1. New View: `resources/views/admin/bookings/single-page.blade.php`**
- Complete single-page booking interface
- 4-step wizard layout
- Responsive design with modern UI
- JavaScript for step navigation and validation
- AJAX integration for seamless flow
- Payment calculator logic
- Employee permission filtering

#### **2. Updated Controller: `app/Http/Controllers/Admin/BookingController.php`**
Added methods:
- `getEmployeeAuthorizedRoutes()` - Retrieves employee permissions
- Updated `create()` - Now returns single-page view with permissions
- Updated `getAvailableTimes()` - Validates employee terminal permissions
- Updated route query - Filters routes based on employee permissions

#### **3. Updated Model: `app/Models/EmployeeRoute.php`**
Added:
- Fillable properties
- Relationships (user, route, startingTerminal)
- Boolean casting for is_active

#### **4. Updated View: `resources/views/admin/bookings/index.blade.php`**
Added:
- "Create Booking" button in header
- Links to new single-page booking system

### Database Structure

The `employee_routes` table:
```
- id
- user_id (foreign key to users)
- route_id (foreign key to routes)
- starting_terminal_id (foreign key to terminals)
- is_active (boolean)
- created_at
- updated_at
```

## Permission Flow

### For Super Admin/Admin:
1. ✅ All terminals available
2. ✅ All routes available
3. ✅ No restrictions

### For Employee:
1. System loads employee's assigned routes
2. Filters "From Terminal" dropdown to only show authorized terminals
3. When loading times, filters routes to only those assigned to employee
4. If employee tries to book from unauthorized terminal → Error
5. If employee tries to book unauthorized route → Error (filtered out)

## Usage Instructions

### For Administrators:
1. Go to Booking Management
2. Click "Create Booking"
3. Complete all 4 steps:
   - Search for trip
   - Select seats
   - Enter passenger details
   - Process payment
4. Confirm booking
5. Booking is created and visible in bookings list

### For Employees:
1. Ensure you have route assignments (contact admin if not)
2. Go to Booking Management
3. Click "Create Booking"
4. You'll see your authorized routes listed
5. Select FROM terminal (must be one you're assigned to)
6. Complete booking flow
7. System validates your permissions at each step

## Security Features

1. **Backend Validation** - All permissions checked server-side
2. **Frontend Filtering** - UI disables unauthorized options
3. **AJAX Protection** - Each AJAX request validates permissions
4. **Role Checking** - Uses Laravel's role system
5. **Session Validation** - Ensures user is authenticated

## Payment Status Logic

```php
if (amount_received >= grand_total) {
    status = 'paid'
    booking_status = 'confirmed'
} else if (amount_received > 0) {
    status = 'pending'
    booking_status = 'pending'
    // Show warning to user
}
```

## Route Configuration

The booking system uses this route:
```php
Route::get('/bookings/create/search', [AdminBookingController::class, 'create'])
    ->name('bookings.create');
```

## Testing Checklist

### As Super Admin:
- ✅ Can select any terminal
- ✅ Can see all routes
- ✅ Can complete full booking flow
- ✅ Payment calculator works correctly
- ✅ Booking is created successfully

### As Employee (With Permissions):
- ✅ See authorized routes listed
- ✅ Can only select assigned terminals
- ✅ Can only see assigned routes
- ✅ Can complete booking for authorized routes
- ✅ Booking is created successfully

### As Employee (Without Permissions):
- ✅ See "No Route Assignments" warning
- ✅ All terminals are disabled
- ✅ Cannot proceed with booking
- ✅ Directed to contact administrator

## Benefits

1. **Single Page Experience** - No page reloads, seamless flow
2. **Clear Progress** - Users always know where they are
3. **Permission Control** - Employees can only book for their terminals/routes
4. **Terminal Tracking** - System knows which terminal created the booking
5. **Live Calculations** - Fare and payment calculated in real-time
6. **Validation** - Comprehensive validation at each step
7. **Professional UI** - Modern, gradient design with smooth animations
8. **Mobile Friendly** - Responsive layout works on all devices
9. **Error Handling** - Clear error messages and warnings
10. **Audit Trail** - Tracks which employee created booking from which terminal

## Future Enhancements (Optional)

1. Add seat locking with Ably (already implemented in other views)
2. Print ticket functionality after booking creation
3. SMS/Email confirmation to passengers
4. Employee performance dashboard
5. Terminal-wise booking reports
6. Shift management for employees
7. Commission calculation for employees

## Troubleshooting

**Issue:** Employee can't see any terminals
**Solution:** Admin needs to assign routes to employee via employee_routes table

**Issue:** No times showing after clicking "Load Available Times"
**Solution:** Check that timetables exist for the selected route

**Issue:** Calculator not updating
**Solution:** Ensure JavaScript is loaded correctly and no console errors

**Issue:** Booking not saving
**Solution:** Check validation errors in network tab, ensure all required fields filled

## Support

For issues or questions about the single-page booking system:
1. Check this documentation
2. Review employee route assignments in database
3. Check Laravel logs for errors
4. Verify user has correct role assigned

