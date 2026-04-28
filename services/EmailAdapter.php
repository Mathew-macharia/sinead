<?php
/**
 * EmailAdapter — Adapter Pattern (Concrete Adapter)
 *
 * Adapts PHP's built-in mail() function to the NotificationInterface.
 * All send attempts (success or failure) are logged to notification_log.
 *
 * To switch to PHPMailer or SendGrid later, replace only this class —
 * nothing else in the application changes.
 *
 * @package    Sinead
 * @subpackage Services
 */
class EmailAdapter implements NotificationInterface
{
    public function getChannel(): string
    {
        return 'email';
    }

    public function send(string $recipient, string $subject, string $message): bool
    {
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $this->log($recipient, $subject, $message, 'failed');
            return false;
        }

        $headers  = 'From: ' . APP_NAME . ' <' . HOTEL_EMAIL . ">\r\n";
        $headers .= 'Reply-To: ' . HOTEL_EMAIL . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        $sent = mail($recipient, $subject, $message, $headers);

        $this->log($recipient, $subject, $message, $sent ? 'sent' : 'failed');

        return $sent;
    }

    private function log(string $recipient, string $subject, string $message, string $status): void
    {
        try {
            Database::getInstance()->prepare("
                INSERT INTO notification_log (channel, recipient, subject, message, status)
                VALUES ('email', :recipient, :subject, :message, :status)
            ")->execute([
                ':recipient' => $recipient,
                ':subject'   => $subject,
                ':message'   => $message,
                ':status'    => $status,
            ]);
        } catch (PDOException $e) {
            error_log('EmailAdapter log error: ' . $e->getMessage());
        }
    }
}
