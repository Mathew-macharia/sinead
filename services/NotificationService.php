<?php
/**
 * NotificationService — Adapter Pattern (Client / Dispatcher)
 *
 * Holds one or more NotificationInterface adapters and dispatches guest
 * notifications across all registered channels in one call.
 *
 * Each adapter decides which field of the $guest array to use as the
 * recipient: 'email' channel reads $guest['email'], 'sms' reads $guest['phone'].
 * If the guest has no value for a channel's field, that channel is skipped.
 *
 * Usage:
 *   $notifier = makeNotifier();
 *   $notifier->notifyGuest($guestRow, 'Booking Confirmed', $htmlMessage);
 *
 * @package    Sinead
 * @subpackage Services
 */
class NotificationService
{
    /** @var NotificationInterface[] */
    private array $adapters = [];

    /**
     * Register a notification channel adapter.
     * Returns $this to allow fluent chaining.
     */
    public function addAdapter(NotificationInterface $adapter): self
    {
        $this->adapters[] = $adapter;
        return $this;
    }

    /**
     * Send a notification to a guest across all registered channels.
     *
     * @param array  $guest   Associative array with at least 'email' and/or 'phone' keys
     * @param string $subject Notification subject / title
     * @param string $message Notification body (HTML for email, plain text for SMS)
     */
    public function notifyGuest(array $guest, string $subject, string $message): void
    {
        foreach ($this->adapters as $adapter) {
            $recipient = $adapter->getChannel() === 'email'
                ? ($guest['email'] ?? '')
                : ($guest['phone'] ?? '');

            if ($recipient !== '') {
                $adapter->send($recipient, $subject, $message);
            }
        }
    }
}

/**
 * Factory helper: build the default NotificationService with all channels.
 * Call this anywhere a notifier is needed — adapters are registered here once.
 */
function makeNotifier(): NotificationService
{
    return (new NotificationService())
        ->addAdapter(new EmailAdapter())
        ->addAdapter(new SMSAdapter());
}
