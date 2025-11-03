<?php

namespace App\Services;

use App\Models\Discount;
use App\Models\Route;
use Carbon\Carbon;

class DiscountService
{
    /**
     * Find applicable discounts for a route, platform, and booking date/time.
     *
     * @param  string  $platform  (web, android, ios, counter)
     */
    public function findApplicableDiscounts(Route $route, string $platform = 'web', ?Carbon $bookingDate = null, ?Carbon $bookingTime = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Discount::where('route_id', $route->id)
            ->where('is_active', true);

        // Apply platform filter using scope
        $query = $query->forPlatform($platform);

        // Get all potential discounts first
        $discounts = $query->get();

        // Filter by date/time validity
        return $discounts->filter(function ($discount) use ($platform, $bookingDate, $bookingTime) {
            return $discount->isValidForBooking($platform, $bookingDate, $bookingTime);
        });
    }

    /**
     * Get the best applicable discount (highest value) for a route and platform.
     */
    public function getBestDiscount(Route $route, float $orderAmount, string $platform = 'web', ?Carbon $bookingDate = null, ?Carbon $bookingTime = null): ?Discount
    {
        $discounts = $this->findApplicableDiscounts($route, $platform, $bookingDate, $bookingTime);

        if ($discounts->isEmpty()) {
            return null;
        }

        // Calculate discount amount for each and find the best one
        $bestDiscount = null;
        $bestAmount = 0;

        foreach ($discounts as $discount) {
            $discountAmount = $discount->calculateDiscount($orderAmount);
            if ($discountAmount > $bestAmount) {
                $bestAmount = $discountAmount;
                $bestDiscount = $discount;
            }
        }

        return $bestDiscount;
    }

    /**
     * Calculate discount amount for a given order amount using applicable discounts.
     *
     * @return array{amount: float, discount: Discount|null, type: string|null, value: float|null}
     */
    public function calculateDiscountForBooking(Route $route, float $orderAmount, string $platform = 'web', ?Carbon $bookingDate = null, ?Carbon $bookingTime = null): array
    {
        $discount = $this->getBestDiscount($route, $orderAmount, $platform, $bookingDate, $bookingTime);

        if (! $discount) {
            return [
                'amount' => 0,
                'discount' => null,
                'type' => null,
                'value' => null,
            ];
        }

        $discountAmount = $discount->calculateDiscount($orderAmount);

        return [
            'amount' => round($discountAmount, 2),
            'discount' => $discount,
            'type' => $discount->discount_type,
            'value' => (float) $discount->value,
        ];
    }
}
