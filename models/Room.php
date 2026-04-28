<?php
/**
 * Room — Abstract Base Class (Factory Pattern)
 *
 * Defines the contract every room type must fulfil. Concrete subclasses
 * (StandardRoom, DeluxeRoom, SuiteRoom) supply type-specific behaviour such
 * as max occupancy, amenities list, and housekeeping priority.
 *
 * Usage:
 *   $room = RoomFactory::fromDbRow($row);  // returns the correct subclass
 *   $room->getAmenities();                 // type-specific list
 *   $room['room_number'];                  // backward-compatible array access
 *
 * @package    Sinead
 * @subpackage Models
 */
abstract class Room implements ArrayAccess
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    // ─── Abstract interface — each subclass must implement ───────────────────

    /** Maximum number of guests this room type can accommodate. */
    abstract public function getMaxOccupancy(): int;

    /** List of amenities included with this room type. */
    abstract public function getAmenities(): array;

    /**
     * Suggested housekeeping task priority after checkout.
     * Returns one of: 'Low', 'Medium', 'High'
     */
    abstract public function getHousekeepingPriority(): string;

    /** Human-readable label, e.g. "Deluxe Room". */
    abstract public function getTypeLabel(): string;

    // ─── Concrete accessors shared by all room types ─────────────────────────

    public function getId(): int             { return (int) ($this->data['id'] ?? 0); }
    public function getRoomNumber(): string  { return $this->data['room_number'] ?? ''; }
    public function getType(): string        { return $this->data['type'] ?? ''; }
    public function getFloor(): int          { return (int) ($this->data['floor'] ?? 1); }
    public function getPricePerNight(): float{ return (float) ($this->data['price_per_night'] ?? 0.0); }
    public function getStatus(): string      { return $this->data['status'] ?? 'Available'; }
    public function getDescription(): string { return $this->data['description'] ?? ''; }

    /** Return the underlying raw data array (useful for passing to views). */
    public function toArray(): array { return $this->data; }

    // ─── ArrayAccess — lets views use $room['room_number'] without changes ───

    public function offsetExists(mixed $offset): bool       { return isset($this->data[$offset]); }
    public function offsetGet(mixed $offset): mixed         { return $this->data[$offset] ?? null; }
    public function offsetSet(mixed $offset, mixed $value): void { $this->data[$offset] = $value; }
    public function offsetUnset(mixed $offset): void        { unset($this->data[$offset]); }
}
