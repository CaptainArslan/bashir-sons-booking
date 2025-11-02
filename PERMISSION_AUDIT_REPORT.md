# Permission Audit Report & Implementation Guide

## Current Status

### ✅ What's Working
- Routes are protected with `->can()` middleware in `routes/web.php`
- Spatie Laravel Permission package is properly installed
- Super Admin bypass is configured in AppServiceProvider
- Base authorization infrastructure is in place

### ❌ What Needs Fixing

#### 1. Frontend Views - Missing @can Directives
**Problem**: Buttons, links, and action elements in Blade views don't check permissions before displaying.

**Files Affected**: All admin views in `resources/views/admin/`

**Pattern to Apply**:
```blade
{{-- Instead of this --}}
<a href="{{ route('admin.bookings.edit', $booking) }}" class="btn btn-warning">Edit</a>

{{-- Use this --}}
@can('edit bookings')
    <a href="{{ route('admin.bookings.edit', $booking) }}" class="btn btn-warning">Edit</a>
@endcan
```

#### 2. DataTable Action Columns
**Problem**: Action buttons in DataTable columns show for all users regardless of permissions.

**Solution Applied**: Updated `BookingController::getData()` to check permissions before generating action buttons.

#### 3. Controller Methods - Missing authorize() Calls
**Problem**: While routes are protected, controllers should also have `authorize()` calls for defense in depth.

**Pattern to Apply**:
```php
public function edit(Booking $booking): View
{
    $this->authorize('edit', $booking); // or $this->authorize('edit bookings');
    // ... rest of method
}
```

## Permission Structure

### Booking Permissions
- `view bookings` - View booking lists and details
- `create bookings` - Create new bookings (console access)
- `edit bookings` - Edit existing bookings
- `delete bookings` - Delete bookings

### Other Resource Permissions (from routes/web.php)
- `view roles`, `create roles`, `edit roles`, `delete roles`
- `view permissions`
- `view cities`, `create cities`, `edit cities`, `delete cities`
- `view terminals`, `create terminals`, `edit terminals`, `delete terminals`
- `manage users` (for employees)
- `view users`, `create users`, `edit users`, `delete users`
- `view bus types`, `create bus types`, `edit bus types`, `delete bus types`
- `view bus layouts`, `create bus layouts`, `edit bus layouts`, `delete bus layouts`
- `view facilities`, `create facilities`, `edit facilities`, `delete facilities`
- `view buses`, `create buses`, `edit buses`, `delete buses`
- `view banners`, `create banners`, `edit banners`, `delete banners`
- `view general settings`, `create general settings`, `edit general settings`, `delete general settings`
- `view enquiries`, `delete enquiries`
- `view routes`, `create routes`, `edit routes`, `delete routes`
- `view route stops`
- `view fares`, `create fares`, `edit fares`, `delete fares`
- `view timetables`, `create timetables`, `edit timetables`, `delete timetables`
- `view announcements`, `create announcements`, `edit announcements`, `delete announcements`
- `view discounts`, `create discounts`, `edit discounts`, `delete discounts`

## Implementation Priority

### Phase 1: Critical Security (Bookings) - COMPLETED ✅
- [x] Booking index view - New Booking button
- [x] Booking DataTable actions column
- [x] Booking edit view - Save/Cancel buttons with permission checks
- [x] BookingController methods - Added authorize() calls:
  - [x] consoleIndex() - create bookings
  - [x] show() - view bookings
  - [x] printTicket() - view bookings
  - [x] edit() - edit bookings
  - [x] update() - edit bookings
  - [x] store() - create bookings
  - [x] destroy() - delete bookings
- [ ] Booking show view - Print/Edit buttons (if any exist)
- [ ] Booking console - All action buttons (if any need protection)

### Phase 2: High Priority Resources
- [ ] Routes management
- [ ] Timetables
- [ ] Users & Employees
- [ ] Fares

### Phase 3: Medium Priority Resources
- [ ] Buses & Bus Types
- [ ] Announcements
- [ ] Discounts
- [ ] Terminals

### Phase 4: Low Priority Resources
- [ ] Cities
- [ ] Facilities
- [ ] Banners
- [ ] Settings

## Files Modified So Far

1. `resources/views/admin/bookings/index.blade.php` - Added @can for New Booking button
2. `app/Http/Controllers/Admin/BookingController.php` - Added:
   - Permission checks in DataTable actions column
   - authorize() calls in all controller methods
3. `resources/views/admin/bookings/edit.blade.php` - Added @can for Save button with fallback message

## Next Steps

1. Continue fixing booking-related views
2. Add authorize() calls in BookingController methods
3. Create a Blade component for permission-wrapped buttons
4. Systematically fix all other views
5. Add controller authorization checks across all controllers

