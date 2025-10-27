# Broadcasting Setup Guide (Pusher/Ably)

This guide explains how to set up real-time seat locking using Laravel Broadcasting with Pusher or Ably.

---

## 📋 Overview

The booking system uses **Laravel Broadcasting** to provide real-time seat updates across multiple users. When one employee selects a seat, other employees immediately see it as "locked" (yellow).

### Key Features:
- ✅ Real-time seat locking via Pusher/Ably
- ✅ Gender-based color coding (blue for male, pink for female)
- ✅ Automatic unlock on page close
- ✅ 10-minute auto-expiry for locks
- ✅ Fallback polling mechanism

---

## 🚀 Setup Options

### Option 1: Using Pusher

#### 1. Create Pusher Account
1. Go to [https://pusher.com](https://pusher.com)
2. Create a free account
3. Create a new Channels app
4. Note your credentials:
   - `app_id`
   - `key`
   - `secret`
   - `cluster`

#### 2. Install Pusher PHP SDK
```bash
composer require pusher/pusher-php-server
```

#### 3. Install Laravel Echo & Pusher JS
```bash
npm install --save-dev laravel-echo pusher-js
```

#### 4. Update `.env`
```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_key
PUSHER_APP_SECRET=your_secret
PUSHER_APP_CLUSTER=your_cluster
PUSHER_SCHEME=https
PUSHER_APP_HOST=
PUSHER_APP_PORT=443
```

#### 5. Configure `config/broadcasting.php`
```php
'pusher' => [
    'driver' => 'pusher',
    'key' => env('PUSHER_APP_KEY'),
    'secret' => env('PUSHER_APP_SECRET'),
    'app_id' => env('PUSHER_APP_ID'),
    'options' => [
        'cluster' => env('PUSHER_APP_CLUSTER'),
        'encrypted' => true,
        'host' => env('PUSHER_APP_HOST') ?: 'api-'.env('PUSHER_APP_CLUSTER', 'mt1').'.pusher.com',
        'port' => env('PUSHER_APP_PORT', 443),
        'scheme' => env('PUSHER_SCHEME', 'https'),
    ],
],
```

---

### Option 2: Using Ably

#### 1. Create Ably Account
1. Go to [https://ably.com](https://ably.com)
2. Create a free account
3. Create a new app
4. Note your API key

#### 2. Install Ably PHP SDK
```bash
composer require ably/ably-php
```

#### 3. Install Laravel Echo & Ably JS
```bash
npm install --save-dev laravel-echo ably
```

#### 4. Update `.env`
```env
BROADCAST_DRIVER=ably

ABLY_KEY=your_ably_api_key
```

#### 5. Configure `config/broadcasting.php`
```php
'ably' => [
    'driver' => 'ably',
    'key' => env('ABLY_KEY'),
],
```

---

## 🛠️ Frontend Setup

### 1. Initialize Laravel Echo

Add to your `resources/js/bootstrap.js`:

#### For Pusher:
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

#### For Ably:
```javascript
import Echo from 'laravel-echo';
import * as Ably from 'ably';

window.Ably = Ably;

window.Echo = new Echo({
    broadcaster: 'ably',
    key: import.meta.env.VITE_ABLY_KEY,
});
```

### 2. Update `.env` with Vite Variables

#### For Pusher:
```env
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_APP_HOST}"
VITE_PUSHER_PORT="${PUSHER_APP_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

#### For Ably:
```env
VITE_ABLY_KEY="${ABLY_KEY}"
```

### 3. Build Frontend Assets
```bash
npm run dev
# or for production
npm run build
```

---

## 📡 How It Works

### Backend Flow

1. **User Selects Seat**
   - Frontend sends POST request to `/bookings/lock-seat`
   - Backend validates seat availability
   - Stores lock in Cache with 10-minute TTL
   - Broadcasts `SeatLocked` event

2. **User Deselects Seat**
   - Frontend sends POST request to `/bookings/unlock-seat`
   - Backend removes lock from Cache
   - Broadcasts `SeatReleased` event

3. **Cache Structure**
```php
Key: "seat_lock:{trip_id}:{seat_number}:{from_stop_id}:{to_stop_id}"
Value: {
    user_id: 123,
    user_name: "John Doe",
    session_id: "abc123...",
    gender: "male",
    locked_at: "2025-10-27 10:30:00"
}
TTL: 10 minutes
```

### Frontend Flow

1. **Page Load**
   - Subscribe to `trip.{trip_id}` channel
   - Listen for `seat.locked` and `seat.released` events
   - Start fallback polling (3-second interval)

2. **Real-Time Updates**
   - When `seat.locked` received → mark seat as yellow (locked)
   - When `seat.released` received → mark seat as green (available)
   - Visual updates happen instantly without page refresh

3. **Page Unload**
   - Unlock all selected seats
   - Leave the broadcast channel
   - Stop polling

---

## 🎯 Events

### SeatLocked Event
```php
Channel: trip.{trip_id}
Event: seat.locked

Payload: {
    trip_id: 1,
    seat_number: "15",
    from_stop_id: 5,
    to_stop_id: 10,
    gender: "male",
    user_name: "John Doe",
    session_id: "abc123...",
    timestamp: "2025-10-27T10:30:00.000Z"
}
```

### SeatReleased Event
```php
Channel: trip.{trip_id}
Event: seat.released

Payload: {
    trip_id: 1,
    seat_number: "15",
    from_stop_id: 5,
    to_stop_id: 10,
    session_id: "abc123...",
    timestamp: "2025-10-27T10:31:00.000Z"
}
```

---

## 🧪 Testing

### 1. Test Broadcasting Connection

Open browser console and check:
```javascript
console.log(Echo);
// Should show Echo instance

Echo.channel('test-channel')
    .listen('.test-event', (data) => {
        console.log('Test event received:', data);
    });
```

### 2. Test Seat Locking

1. Open booking page in **two different browsers**
2. Select same trip/date/terminals in both
3. Click seat in Browser 1 → should turn blue/pink
4. Browser 2 should immediately show seat as yellow (locked)
5. Deselect seat in Browser 1 → Browser 2 shows green (available)

### 3. Check Pusher/Ably Dashboard

- View real-time events in your Pusher/Ably dashboard
- Monitor message counts and connections
- Check for errors or failed deliveries

---

## 🐛 Troubleshooting

### Issue: Echo is undefined
**Solution:** Run `npm run build` and clear browser cache

### Issue: Events not received
**Solution:** 
1. Check `.env` credentials are correct
2. Verify `BROADCAST_DRIVER=pusher` or `ably`
3. Check browser console for connection errors
4. Ensure queue worker is running: `php artisan queue:work`

### Issue: Seat stays locked forever
**Solution:** Cache TTL auto-expires after 10 minutes. Or manually clear:
```bash
php artisan cache:clear
```

### Issue: CORS errors
**Solution:** Add your domain to Pusher/Ably allowed origins

### Issue: Multiple connections
**Solution:** Ensure you're calling `Echo.leave('trip.' + tripId)` on page unload

---

## ⚡ Performance

### Recommended Settings

**Pusher Free Tier:**
- 100 concurrent connections
- 200,000 messages/day
- Perfect for small to medium deployments

**Ably Free Tier:**
- 6 million messages/month
- Unlimited connections
- Better for larger deployments

### Optimization Tips

1. **Use presence channels** for tracking active users
2. **Implement exponential backoff** for reconnections
3. **Batch seat updates** if selecting multiple seats
4. **Use private channels** for security (optional)
5. **Monitor message rates** to stay within limits

---

## 🔒 Security

### Current Implementation
- ✅ Session-based ownership validation
- ✅ Server-side seat availability checks
- ✅ Automatic lock expiry (10 minutes)
- ✅ Broadcasting uses `toOthers()` to avoid self-notifications

### Additional Security (Optional)

#### Private Channels
Update `routes/channels.php`:
```php
Broadcast::channel('trip.{tripId}', function ($user, $tripId) {
    // Only authenticated employees can listen
    return $user->hasRole('employee');
});
```

Update `SeatLocked.php` event:
```php
public function broadcastOn(): Channel
{
    return new PrivateChannel('trip.'.$this->tripId);
}
```

Update frontend:
```javascript
Echo.private('trip.' + tripId)
    .listen('.seat.locked', (data) => {
        // ...
    });
```

---

## 📊 Monitoring

### Key Metrics to Track
1. **Connection Count** - Active users on seat selection page
2. **Message Rate** - Events per minute
3. **Lock Duration** - How long seats stay locked
4. **Failed Locks** - Seats already taken errors

### Logging
Check Laravel logs:
```bash
tail -f storage/logs/laravel.log
```

---

## 🎨 Customization

### Change Lock Duration
In `BookingController.php`:
```php
Cache::put($lockKey, $lockValue, now()->addMinutes(15)); // 15 minutes
```

### Change Polling Interval
In `select-seats.blade.php`:
```javascript
pollingInterval = setInterval(function() {
    checkSeatAvailability();
}, 5000); // 5 seconds
```

### Custom Event Names
In `SeatLocked.php`:
```php
public function broadcastAs(): string
{
    return 'custom.seat.locked';
}
```

---

## ✅ Checklist

- [ ] Pusher/Ably account created
- [ ] `.env` updated with credentials
- [ ] Composer packages installed
- [ ] NPM packages installed
- [ ] `bootstrap.js` configured
- [ ] `npm run build` executed
- [ ] Broadcasting tested in multiple browsers
- [ ] Queue worker running (if using queues)
- [ ] Dashboard monitoring active

---

## 📚 Additional Resources

- [Laravel Broadcasting Docs](https://laravel.com/docs/broadcasting)
- [Pusher Channels Docs](https://pusher.com/docs/channels)
- [Ably Documentation](https://ably.com/documentation)
- [Laravel Echo Docs](https://laravel.com/docs/broadcasting#client-side-installation)

---

**Need Help?** Check the Laravel logs and browser console for detailed error messages.


