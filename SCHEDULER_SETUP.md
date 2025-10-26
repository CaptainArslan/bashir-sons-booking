# Scheduler Setup Guide - Bus Booking System

## 📋 Overview

This guide will help you set up automated jobs for the bus booking system to handle trip lifecycle management, seat lock synchronization, and phone booking releases.

---

## 🔧 Step 1: Configure Scheduler

Add these scheduled tasks to `routes/console.php`:

```php
<?php

use App\Jobs\CompleteTripJob;
use App\Jobs\ReleasePhoneBookingsJob;
use App\Jobs\StartTripJob;
use App\Jobs\SyncSeatLocksJob;
use Illuminate\Support\Facades\Schedule;

// Sync seat locks every 5 minutes
Schedule::job(new SyncSeatLocksJob())
    ->everyFiveMinutes()
    ->name('sync-seat-locks')
    ->withoutOverlapping();

// Release phone bookings (runs every minute, checks 30min before departure)
Schedule::job(new ReleasePhoneBookingsJob(30))
    ->everyMinute()
    ->name('release-phone-bookings')
    ->withoutOverlapping();

// Start trips at departure time
Schedule::job(new StartTripJob())
    ->everyMinute()
    ->name('start-trips')
    ->withoutOverlapping();

// Complete trips at arrival time
Schedule::job(new CompleteTripJob())
    ->everyFiveMinutes()
    ->name('complete-trips')
    ->withoutOverlapping();

// Optional: Mark trips as boarding 30 minutes before departure
Schedule::call(function () {
    app(\App\Services\TripLifecycleService::class)->processBoardingTrips(30);
})
    ->everyMinute()
    ->name('process-boarding-trips');

// Optional: Mark delayed trips
Schedule::call(function () {
    app(\App\Services\TripLifecycleService::class)->processDelayedTrips();
})
    ->everyFiveMinutes()
    ->name('process-delayed-trips');

// Optional: Generate trips from timetables for next week
Schedule::call(function () {
    $tripService = app(\App\Services\TripService::class);
    $tripService->generateTripsFromTimetables(
        now()->format('Y-m-d'),
        now()->addDays(7)->format('Y-m-d')
    );
})
    ->dailyAt('01:00')
    ->name('generate-trips-from-timetables');
```

---

## 🚀 Step 2: Start the Scheduler

### Development Environment

```bash
php artisan schedule:work
```

This will run the scheduler in the foreground. Keep this terminal open.

### Production Environment

Add a cron entry on your server:

```bash
crontab -e
```

Add this line:

```cron
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Or with logging:

```cron
* * * * * cd /path-to-your-project && php artisan schedule:run >> /var/log/laravel-scheduler.log 2>&1
```

---

## 🔄 Step 3: Configure Queue Workers

The scheduled jobs will dispatch to queues. You need queue workers running:

### Development

```bash
php artisan queue:work --tries=3
```

### Production (Supervisor)

Create `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path-to-your-project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/laravel-worker.log
stopwaitsecs=3600
```

Then:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

---

## 🧪 Step 4: Test the Scheduler

### View Scheduled Tasks

```bash
php artisan schedule:list
```

### Test Individual Commands

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

### Monitor Logs

```bash
tail -f storage/logs/laravel.log
```

---

## 📊 Job Details

### 1. SyncSeatLocksJob (Every 5 Minutes)

**Purpose**: Sync Redis seat locks with database

**What it does**:
- Releases expired locks from Redis
- Updates database records for released locks
- Cleans up stale Redis keys

**Monitoring**:
```php
// Check sync stats in logs
Log::info('Synced seat locks', [
    'expired_released' => 5,
    'redis_cleaned' => 2,
    'database_updated' => 3
]);
```

### 2. ReleasePhoneBookingsJob (Every Minute)

**Purpose**: Auto-cancel phone bookings not confirmed 30min before departure

**What it does**:
- Finds trips departing in next 30 minutes
- Cancels pending phone bookings
- Releases associated seat locks

**Configuration**:
```php
// Change time window (default 30 minutes)
Schedule::job(new ReleasePhoneBookingsJob(45))->everyMinute();
```

### 3. StartTripJob (Every Minute)

**Purpose**: Auto-start trips at departure time

**What it does**:
- Finds scheduled/boarding trips at departure time
- Checks if bus is assigned
- Changes status to "ongoing"

**Requirements**:
- Trip must have bus assigned
- Status must be scheduled or boarding

### 4. CompleteTripJob (Every 5 Minutes)

**Purpose**: Auto-complete trips at arrival time

**What it does**:
- Finds ongoing trips past estimated arrival
- Changes status to "completed"

**Requirements**:
- Trip must be ongoing
- Must have estimated_arrival_datetime

---

## 🔍 Troubleshooting

### Jobs Not Running

1. **Check scheduler is running**:
   ```bash
   php artisan schedule:work
   ```

2. **Check queue workers**:
   ```bash
   php artisan queue:work
   ```

3. **Check Redis connection**:
   ```bash
   php artisan tinker
   >>> Redis::ping()
   ```

### Jobs Failing

1. **Check logs**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Check failed jobs**:
   ```bash
   php artisan queue:failed
   ```

3. **Retry failed jobs**:
   ```bash
   php artisan queue:retry all
   ```

### Jobs Running Multiple Times

1. Ensure `withoutOverlapping()` is used
2. Check if multiple schedulers are running
3. Verify cron isn't duplicated

---

## 📈 Performance Optimization

### 1. Use Horizon (Recommended)

```bash
composer require laravel/horizon
php artisan horizon:install
php artisan horizon
```

Benefits:
- Better queue monitoring
- Auto-scaling workers
- Failed job management
- Metrics and insights

### 2. Queue Priority

```php
Schedule::job(new ReleasePhoneBookingsJob(30))
    ->everyMinute()
    ->onQueue('high');

Schedule::job(new SyncSeatLocksJob())
    ->everyFiveMinutes()
    ->onQueue('low');
```

Then run workers with priority:
```bash
php artisan queue:work --queue=high,default,low
```

### 3. Memory Management

```bash
php artisan queue:work --memory=512 --timeout=300
```

---

## 🎯 Best Practices

1. **Always use `withoutOverlapping()`** to prevent concurrent runs
2. **Log important events** for debugging
3. **Monitor failed jobs** regularly
4. **Set appropriate timeouts** for long-running jobs
5. **Use queue priorities** for critical jobs
6. **Test in staging** before production deployment

---

## 📝 Manual Triggers

### Trigger Trip Lifecycle Update

```php
php artisan tinker
>>> $service = app(\App\Services\TripLifecycleService::class);
>>> $service->processAllAutomaticUpdates();
```

### Force Seat Lock Sync

```php
>>> $service = app(\App\Services\SeatService::class);
>>> $service->syncSeatLocks();
```

### Generate Trips from Timetables

```php
>>> $service = app(\App\Services\TripService::class);
>>> $count = $service->generateTripsFromTimetables('2025-10-27', '2025-11-03');
>>> echo "{$count} trips generated";
```

---

## ✅ Verification Checklist

- [ ] Scheduler is running (`php artisan schedule:work` or cron configured)
- [ ] Queue workers are running (`php artisan queue:work`)
- [ ] Redis is accessible
- [ ] Logs are being written
- [ ] Test job execution manually
- [ ] Monitor for failed jobs
- [ ] Set up supervisor for production
- [ ] Configure log rotation
- [ ] Set up alerting for failed jobs (optional)

---

**Your automated system is now ready to handle trip lifecycle, seat locks, and booking management! 🚀**

