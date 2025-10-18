<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\BusLayoutEnum;
use App\Enums\SeatTypeEnum;
use App\Enums\GenderEnum;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class BusLayout extends Model
{
    /** @use HasFactory<\Database\Factories\BusLayoutFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'total_rows',
        'total_columns',
        'total_seats',
        'seat_map',
        'status',
    ];

    protected $casts = [
        'status' => BusLayoutEnum::class,
        'seat_map' => 'array',
    ];

    // =============================
    // Relationships
    // =============================
    public function buses(): HasMany
    {
        return $this->hasMany(Bus::class);
    }

    // =============================
    // Accessors & Mutators
    // =============================
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn($value) => ucfirst($value),
        );
    }

    protected function totalSeats(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->total_rows * $this->total_columns ?? 0,
        );
    }

    // =============================
    // Seat Map Methods
    // =============================
    
    /**
     * Generate default seat map based on rows and columns
     */
    public function generateDefaultSeatMap(): array
    {
        $seatMap = [];
        $seatNumber = 1;
        
        for ($row = 0; $row < $this->total_rows; $row++) {
            $seatMap[$row] = [];
            for ($col = 0; $col < $this->total_columns; $col++) {
                // Determine seat type based on position
                $seatType = $this->determineSeatType($row, $col);
                
                $seatMap[$row][$col] = [
                    'number' => $seatNumber,
                    'type' => $seatType,
                    'gender' => null, // No gender restriction by default
                    'is_reserved_for_female' => false,
                    'is_available' => true,
                    'row' => $row + 1,
                    'column' => $col + 1,
                ];
                $seatNumber++;
            }
        }
        
        return $seatMap;
    }
    
    /**
     * Determine seat type based on position
     */
    private function determineSeatType(int $row, int $col): string
    {
        $totalCols = $this->total_columns;
        
        // Window seats (first and last columns)
        if ($col === 0 || $col === $totalCols - 1) {
            return SeatTypeEnum::WINDOW->value;
        }
        
        // Aisle seats (second and second-to-last columns)
        if ($col === 1 || $col === $totalCols - 2) {
            return SeatTypeEnum::AISLE->value;
        }
        
        // Middle seats
        return SeatTypeEnum::MIDDLE->value;
    }
    
    /**
     * Get seat by number
     */
    public function getSeatByNumber(int $seatNumber): ?array
    {
        if (!$this->seat_map) {
            return null;
        }
        
        foreach ($this->seat_map as $row) {
            foreach ($row as $seat) {
                if ($seat['number'] === $seatNumber) {
                    return $seat;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Update seat properties
     */
    public function updateSeat(int $seatNumber, array $properties): bool
    {
        if (!$this->seat_map) {
            return false;
        }
        
        foreach ($this->seat_map as $rowIndex => $row) {
            foreach ($row as $colIndex => $seat) {
                if ($seat['number'] === $seatNumber) {
                    $this->seat_map[$rowIndex][$colIndex] = array_merge($seat, $properties);
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Get seats by type
     */
    public function getSeatsByType(string $seatType): array
    {
        $seats = [];
        
        if (!$this->seat_map) {
            return $seats;
        }
        
        foreach ($this->seat_map as $row) {
            foreach ($row as $seat) {
                if ($seat['type'] === $seatType) {
                    $seats[] = $seat;
                }
            }
        }
        
        return $seats;
    }
    
    /**
     * Get female-only seats
     */
    public function getFemaleOnlySeats(): array
    {
        $seats = [];
        
        if (!$this->seat_map) {
            return $seats;
        }
        
        foreach ($this->seat_map as $row) {
            foreach ($row as $seat) {
                if ($seat['is_reserved_for_female']) {
                    $seats[] = $seat;
                }
            }
        }
        
        return $seats;
    }
    
    /**
     * Validate seat map structure
     */
    public function validateSeatMap(): array
    {
        $errors = [];
        
        if (!$this->seat_map) {
            $errors[] = 'Seat map is not defined';
            return $errors;
        }
        
        $expectedSeats = $this->total_rows * $this->total_columns;
        $actualSeats = 0;
        $seatNumbers = [];
        
        foreach ($this->seat_map as $rowIndex => $row) {
            if (!is_array($row)) {
                $errors[] = "Row {$rowIndex} is not an array";
                continue;
            }
            
            foreach ($row as $colIndex => $seat) {
                if (!is_array($seat)) {
                    $errors[] = "Seat at row {$rowIndex}, column {$colIndex} is not an array";
                    continue;
                }
                
                $actualSeats++;
                
                // Check required fields
                $requiredFields = ['number', 'type', 'gender', 'is_reserved_for_female', 'is_available'];
                foreach ($requiredFields as $field) {
                    if (!array_key_exists($field, $seat)) {
                        $errors[] = "Seat {$seat['number']} missing required field: {$field}";
                    }
                }
                
                // Check for duplicate seat numbers
                if (in_array($seat['number'], $seatNumbers)) {
                    $errors[] = "Duplicate seat number: {$seat['number']}";
                } else {
                    $seatNumbers[] = $seat['number'];
                }
                
                // Validate seat type
                if (!in_array($seat['type'], SeatTypeEnum::getSeatTypes())) {
                    $errors[] = "Invalid seat type for seat {$seat['number']}: {$seat['type']}";
                }
                
                // Validate gender
                if ($seat['gender'] !== null && !in_array($seat['gender'], GenderEnum::getGenders())) {
                    $errors[] = "Invalid gender for seat {$seat['number']}: {$seat['gender']}";
                }
            }
        }
        
        if ($actualSeats !== $expectedSeats) {
            $errors[] = "Seat count mismatch. Expected: {$expectedSeats}, Actual: {$actualSeats}";
        }
        
        return $errors;
    }
}
