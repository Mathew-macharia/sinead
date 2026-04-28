<?php
/**
 * StandardRoom — Concrete Room (Factory Pattern)
 *
 * Entry-level room with essential amenities, suitable for 1-2 guests.
 * Housekeeping priority after checkout is Low — cleaned in normal rotation.
 *
 * @package    Sinead
 * @subpackage Models
 */
class StandardRoom extends Room
{
    public function getMaxOccupancy(): int
    {
        return 2;
    }

    public function getAmenities(): array
    {
        return [
            'Free WiFi',
            'Air Conditioning',
            'Flat-screen TV',
            'En-suite Bathroom',
            'Daily Housekeeping',
        ];
    }

    public function getHousekeepingPriority(): string
    {
        return 'Low';
    }

    public function getTypeLabel(): string
    {
        return 'Standard Room';
    }
}
