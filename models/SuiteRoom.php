<?php
/**
 * SuiteRoom — Concrete Room (Factory Pattern)
 *
 * Premium suite with panoramic views, butler service, and luxury amenities.
 * Suitable for up to 4 guests. Housekeeping priority after checkout is High —
 * requires thorough preparation before the next booking.
 *
 * @package    Sinead
 * @subpackage Models
 */
class SuiteRoom extends Room
{
    public function getMaxOccupancy(): int
    {
        return 4;
    }

    public function getAmenities(): array
    {
        return [
            'Free WiFi (High-speed)',
            'Air Conditioning',
            'Smart TV (65")',
            'En-suite Bathroom with Jacuzzi',
            'Mini Bar (Fully Stocked)',
            'Panoramic View',
            'Separate Living Room',
            'Lounge Area',
            'Butler Service',
            'Daily Housekeeping (Twice Daily)',
            'Complimentary Breakfast & Evening Drinks',
            'Pillow Menu',
        ];
    }

    public function getHousekeepingPriority(): string
    {
        return 'High';
    }

    public function getTypeLabel(): string
    {
        return 'Suite';
    }
}
