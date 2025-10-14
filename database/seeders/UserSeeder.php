<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $twoFactorSecret = "eyJpdiI6ImFTUldPU0QwNFZmM1FzbUlNeXJRblE9PSIsInZhbHVlIjoicmxjbHg0bVRCQ09pRVJzampkOXNRQUxNVVRTRW1rK1FFdEE2UlowdHpUbz0iLCJtYWMiOiJhNTk4YzljNWEwY2Y2N2NlMDQ1MTVmODZiMWQyZGQ0YmM4ZmE1YTUwYTY0ZDg5NmQ0ODQxNmQyOGNhNzBhOGE3IiwidGFnIjoiIn0";
        $twoFactorRecoveryCodes = "eyJpdiI6IlQvME4rbFdRQUsyUVllc00zMVJ6RWc9PSIsInZhbHVlIjoiaDFlOG9WS3dicFVaT1NWQ1B0SnBua2Q2N3hNdEZ4UUxQUVdXWGZyRE1oaWN0QUlkMitQQ1Y4MUp0MnpaOTFjQTR1aUFHVGV0L1IvYXJXZnN5RFFBSkpuYnptOWpYWmJ1TVluTmJQQWw0dE1xZ3hnMzR0eEROR3REeFZEaHJRdFIiLCJtYWMiOiIwMDJjOTM5Y2IwYmM4YThhYTgxN2FhNGFmY2VlYzQyODBjMmNjNDZkNzMyZjEwZmUwOTNkNjFjNjg1YjY2ZmY3IiwidGFnIjoiIn0";
        $twoFactorConfirmedAt = Carbon::now();

        $user = User::create([
            'name' => 'Omar',
            'email' => 'customer@gmail.com',
            'password' => Hash::make('password'),
            'two_factor_secret' => $twoFactorSecret,
            'two_factor_recovery_codes' => $twoFactorRecoveryCodes,
            'two_factor_confirmed_at' => $twoFactorConfirmedAt,
        ]);
        $user->assignRole('Customer');

        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'two_factor_secret' => $twoFactorSecret,
            'two_factor_recovery_codes' => $twoFactorRecoveryCodes,
            'two_factor_confirmed_at' => $twoFactorConfirmedAt,
        ]);
        $user->assignRole(['Super Admin']);


        $user = User::create([
            'name' => 'Employee',
            'email' => 'employee@gmail.com',
            'password' => Hash::make('password'),
            'two_factor_secret' => $twoFactorSecret,
            'two_factor_recovery_codes' => $twoFactorRecoveryCodes,
            'two_factor_confirmed_at' => $twoFactorConfirmedAt,
        ]);
        $user->assignRole(['Employee']);
    }
}
