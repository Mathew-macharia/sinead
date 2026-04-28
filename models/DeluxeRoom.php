<?php
/**
 * DeluxeRoom — Concrete Room (Factory Pattern)
 *
 * Mid-tier room with enhanced amenities and city views, suitable for up to 3 guests.
 * Housekeeping priority after checkout is Medium.
 *
 * @package    Sinead
 * @subpackage Models
 */
class DeluxeRoom extends Room
{
    public function getMaxOccupancy(): int
    {
        return 3;
    }

    public function getAmenities(): array
    {
        return [
            'Free WiFi',
            'Air Conditioning',
            'Flat-screen TV',
            'En-suite Bathroom',
            'Mini Bar',
            'City View',
            'King Bed',
            'Daily Housekeeping',
            'Complimentary Breakfast',
        ];
    }

    public function getHousekeepingPriority(): string
    {
        return 'Medium';
    }

    public function getTypeLabel(): string
    {
        return 'Deluxe Room';
    }
}
