<?php

namespace App\Traits;

trait AverageRatingTrait
{
    /**
     * Calculate the average rating.
     *
     * @param \Illuminate\Support\Collection $ratings
     * @return float|null
     */
    private function calculateAverageRating($ratings): ?float
    {
        if ($ratings->isEmpty()) {
            return null;
        }

        $totalRating = $ratings->sum('rate');
        $ratingsCount = $ratings->count();

        return round($totalRating / $ratingsCount, 1);
    }
}