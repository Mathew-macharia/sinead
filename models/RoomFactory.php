<?php
/**
 * RoomFactory — Factory Pattern
 *
 * Creates the correct Room subclass based on the room type string.
 * Controllers and other callers never instantiate room classes directly;
 * they always go through this factory, so adding a new room type only
 * requires a new subclass + one extra line here.
 *
 * Usage:
 *   $room = RoomFactory::create('Suite', $dataArray);
 *   $room = RoomFactory::fromDbRow($pdoRow);   // most common call site
 *
 * @package    Sinead
 * @subpackage Models
 */
class RoomFactory
{
    /**
     * Instantiate the right Room subclass for the given type.
     *
     * @param  string $type One of: 'Standard', 'Deluxe', 'Suite'
     * @param  array  $data Raw room data (DB row or form data)
     * @return Room
     * @throws InvalidArgumentException for unknown types
     */
    public static function create(string $type, array $data): Room
    {
        return match ($type) {
            'Standard' => new StandardRoom($data),
            'Deluxe'   => new DeluxeRoom($data),
            'Suite'    => new SuiteRoom($data),
            default    => throw new InvalidArgumentException(
                "Unknown room type '{$type}'. Valid types: Standard, Deluxe, Suite."
            ),
        };
    }

    /**
     * Convenience method: build a Room object directly from a PDO result row.
     * The row must contain a 'type' key (as returned by SELECT * FROM rooms).
     *
     * @param  array $row PDO fetch result
     * @return Room
     */
    public static function fromDbRow(array $row): Room
    {
        return self::create($row['type'], $row);
    }
}
