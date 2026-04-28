<?php
/**
 * SMSAdapter — Adapter Pattern (Stub Adapter)
 *
 * Stub implementation that logs SMS notifications to the notification_log
 * table instead of sending them to a real provider.
 *
 * To go live: replace the body of send() with your provider's SDK call
 * (e.g. Twilio, Africa's Talking) — the interface and all callers stay the same.
 *
 * @package    Sinead
 * @subpackage Services
 */
class SMSAdapter implements NotificationInterface
{
    public function getChannel(): string
    {
        return 'sms';
    }

    public function send(string $recipient, string $subject, string $message): bool
    {
        // Stub: write to notification_log with status 'logged'.
        // Replace this block with a real SMS SDK when a provider is chosen.
        $this->log($recipient, $subject, $message);
        return true;
    }

    private function log(string $recipient, string $subject, string $message): void
    {
        try {
            Database::getInstance()->prepare("
                INSERT INTO notification_log (channel, recipient, subject, message, status)
                VALUES ('sms', :recipient, :subject, :message, 'logged')
            ")->execute([
                ':recipient' => $recipient,
                ':subject'   => $subject,
                ':message'   => $message,
            ]);
        } catch (PDOException $e) {
            error_log('SMSAdapter log error: ' . $e->getMessage());
        }
    }
}
