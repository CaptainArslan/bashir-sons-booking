# Real-time Seat Selection System

This document explains how the real-time seat selection system works with Ably broadcasting.

## Overview

The booking system now includes real-time seat selection with live updates across multiple users. When one user selects a seat, other users see it locked immediately without page refresh.

## Features

### 1. Real-time Seat Locking
- When a user selects a seat, it's locked for 10 minutes
- Other users see the seat as locked in real-time via Ably broadcasting
- Locked seats show a lock icon 🔒 and gender icon (♂ male, ♀ female)
- Hover tooltips show who locked the seat and their gender
- **No polling** - Pure real-time updates via WebSocket

### 2. Seat Status Visualization
- **Available** - Light green background (#f0fff4) with green border
  - Can be clicked to select
  - Hovers to solid green
  
- **Your Selection** - Solid blue (#0d6efd)
  - Seats you've selected
  - Shows gender badge in top-right (♂ male / ♀ female)
  - Click again to deselect
  
- **Locked by Others** - Gray background with dashed border
  - Shows lock icon 🔒 in top-right
  - Shows gender icon (♂/♀) at bottom center
  - Tooltip shows who locked it
  - Updates in real-time via Ably
  
- **Booked (Pending)** - Yellow background (#fff3cd)
  - Shows hourglass icon ⏳
  - Booking exists but payment pending
  - Cannot be selected
  
- **Sold (Confirmed)** - Red background (#dc3545)
  - Shows checkmark icon ✓
  - Payment confirmed
  - Cannot be selected

### 3. Gender-based Selection
- Users select gender (Male/Female) when choosing a seat via modal
- **Your selections** show gender badge in top-right corner
  - White circular badge with ♂ (male) or ♀ (female) icon
  - Badge has colored icon (blue for male, pink for female)
- **Locked seats** (by others) show gender icon at bottom
- Gender is stored and broadcast to other users
- Selection summary shows: "Seat X - Male" or "Seat X - Female"
- Clear visual distinction - always know gender for every seat

### 4. Time Display Enhancements
The booking form now shows upcoming departure times with enhanced display:
- **Today** - Shows "🕐 Today at HH:MM AM/PM"
- **Tomorrow** - Shows "📅 Tomorrow at HH:MM AM/PM"
- **Future dates** - Shows "📅 MM/DD/YYYY at HH:MM AM/PM"
- Route name and code displayed with each time
- Count of available departure times

### 5. Real-time Notifications
Users receive toast notifications when:
- Another user locks a seat
- A locked seat becomes available
- Notifications appear in the top-right corner
- Auto-dismiss after 3 seconds

## Technical Implementation

### Broadcasting Events

Two main events are broadcast via Ably:

#### SeatLocked Event
```php
broadcast(new SeatLocked(
    $tripId,
    $seatNumber,
    $fromStopId,
    $toStopId,
    $gender,
    $userName,
    $sessionId
))->toOthers();
```

#### SeatReleased Event
```php
broadcast(new SeatReleased(
    $tripId,
    $seatNumber,
    $fromStopId,
    $toStopId,
    $sessionId
))->toOthers();
```

### Channel Structure
- Channel name: `trip.{tripId}`
- Events: `.seat.locked` and `.seat.released`
- Uses public channels (no authentication required for admin)

### Frontend Integration

The JavaScript subscribes to the trip channel:

```javascript
Echo.channel('trip.' + tripId)
    .listen('.seat.locked', (data) => {
        // Update UI to show locked seat
        handleSeatLockedEvent(data);
    })
    .listen('.seat.released', (data) => {
        // Update UI to show available seat
        handleSeatReleasedEvent(data);
    });
```

### Real-time Only Design

The system is designed to work exclusively with Ably broadcasting:
- **No polling** - Eliminates server load from periodic requests
- **Instant updates** - Changes appear immediately via WebSocket
- **Efficient** - Only broadcasts when seats are locked/released
- **Connection monitoring** - Alerts users if real-time connection fails

## Setup Instructions

### 1. Environment Configuration

Add these variables to your `.env` file:

```env
BROADCAST_CONNECTION=ably

# Ably Configuration
ABLY_KEY=your-ably-api-key

# For client-side (Vite)
VITE_ABLY_PUBLIC_KEY=your-ably-public-key
```

### 2. Get Ably API Keys

1. Sign up at [https://ably.com](https://ably.com)
2. Create a new app
3. Get your API key from the dashboard
4. The key format is: `{app-id}.{key-id}:{key-secret}`
5. For the public key (VITE_ABLY_PUBLIC_KEY), use just the `{app-id}.{key-id}` part

### 3. Build Assets

After updating environment variables:

```bash
npm run build
# or for development
npm run dev
```

### 4. Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
```

## API Endpoints

### Lock Seat
**POST** `/admin/bookings/lock-seat`

Request:
```json
{
    "trip_id": 1,
    "from_stop_id": 1,
    "to_stop_id": 5,
    "seat_number": "12",
    "gender": "male"
}
```

### Unlock Seat
**POST** `/admin/bookings/unlock-seat`

Request:
```json
{
    "trip_id": 1,
    "from_stop_id": 1,
    "to_stop_id": 5,
    "seat_number": "12"
}
```

### Check Seats (Debugging)
**POST** `/admin/bookings/check-seats`

This endpoint is available for debugging but not used in normal operation since we rely on real-time broadcasting.

Request:
```json
{
    "trip_id": 1,
    "from_stop_id": 1,
    "to_stop_id": 5
}
```

Response:
```json
{
    "success": true,
    "data": {
        "locked_seats": {
            "12": {
                "gender": "male",
                "user_name": "John Doe",
                "locked_at": "2024-01-15 10:30:00"
            }
        },
        "seat_statuses": {
            "5": {
                "status": "confirmed",
                "is_confirmed": true,
                "booking_number": "BK-20240115-001"
            },
            "8": {
                "status": "pending",
                "is_confirmed": false,
                "booking_number": "BK-20240115-002"
            }
        }
    }
}
```

## Cache Keys

Seat locks are stored in cache with this key format:
```
seat_lock:{trip_id}:{seat_number}:{from_stop_id}:{to_stop_id}
```

Lock data structure:
```php
[
    'user_id' => 1,
    'user_name' => 'John Doe',
    'session_id' => 'abc123...',
    'gender' => 'male',
    'locked_at' => '2024-01-15 10:30:00'
]
```

Lock duration: **10 minutes**

## User Experience Flow

1. **Select Terminals & Date**
   - Choose from and to terminals
   - Select departure date
   - System automatically loads available times

2. **Choose Departure Time**
   - View upcoming departure times with smart labels
   - See route details for each time slot
   - Alert shows count of available times

3. **Select Seats**
   - View 45-seat bus layout
   - Click available seats (green)
   - Choose passenger gender (Male/Female modal)
   - Seat immediately turns blue/pink and locks

4. **Real-time Updates**
   - See other users' selections appear as yellow locked seats
   - Receive toast notifications for changes
   - Locked seats show who locked them on hover

5. **Complete Booking**
   - Review selected seats in sidebar
   - See total fare calculation
   - Continue to passenger details

## Troubleshooting

### Echo not connecting
1. Check browser console for errors
2. Verify VITE_ABLY_PUBLIC_KEY is set
3. Ensure assets are built (`npm run build`)
4. Check Ably dashboard for connection logs

### Seats not updating in real-time
1. Check browser console for connection status logs
2. Look for: "✅ Real-time seat updates active via Ably"
3. Check network tab for WebSocket connection to `realtime-pusher.ably.io`
4. Verify broadcasting driver is set to 'ably' in `.env`
5. Confirm Ably credentials are correct
6. If you see "⚠️ Echo is not defined", rebuild assets with `npm run build`

### Lock expires too quickly/slowly
Adjust the lock duration in `BookingController.php`:
```php
Cache::put($lockKey, $lockValue, now()->addMinutes(10));
// Change 10 to desired minutes
```

## Performance Considerations

### Caching Strategy
- Seat locks use cache (Redis/Memcached recommended)
- Database queries are minimized
- Only overlapping segments checked for conflicts

### Broadcasting Optimization
- Events only sent to `trip.{id}` channel
- `toOthers()` prevents echo to sender
- Minimal data in broadcast payload
- Gender info included for UI display

### No Polling Design
- **Zero polling overhead** - No periodic server requests
- **Pure WebSocket** - All updates via Ably real-time connection
- **Instant feedback** - Seats lock/unlock immediately
- **Scalable** - No load from repeated polling requests

## Future Enhancements

Potential improvements:
1. Show active users count on the page
2. Add seat reservation timer countdown
3. Show user avatars on locked seats
4. Add sound notifications for seat changes
5. Implement seat preferences (window/aisle)
6. Add bulk seat selection
7. Show seat map legend with real-time counts

## Related Files

- **Controller**: `app/Http/Controllers/Admin/BookingController.php`
- **Events**: 
  - `app/Events/SeatLocked.php`
  - `app/Events/SeatReleased.php`
- **Views**: 
  - `resources/views/admin/bookings/create.blade.php`
  - `resources/views/admin/bookings/select-seats.blade.php`
- **JavaScript**: 
  - `resources/js/echo.js`
  - `resources/js/bootstrap.js`
- **Config**: `config/broadcasting.php`
- **Routes**: `routes/web.php`
- **Channels**: `routes/channels.php`

## Support

For issues or questions, check:
1. Laravel Broadcasting docs: https://laravel.com/docs/broadcasting
2. Ably Laravel docs: https://ably.com/docs/platforms/laravel
3. Laravel Echo docs: https://laravel.com/docs/broadcasting#receiving-broadcasts

