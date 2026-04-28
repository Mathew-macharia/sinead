<?php
/**
 * NotificationInterface — Adapter Pattern (Target Interface)
 *
 * Every notification channel (email, SMS, push, etc.) must implement this
 * interface. The NotificationService depends only on this contract, so
 * swapping or adding channels requires zero changes to callers.
 *
 * @package    Sinead
 * @subpackage Services
 */
interface NotificationInterface
{
    /**
     * Send a notification to a single recipient.
     *
     * @param  string $recipient  Email address or phone number, depending on channel
     * @param  string $subject    Subject line (used for email; SMS adapters may ignore it)
     * @param  string $message    Message body (may contain HTML for email)
     * @return bool               True if the send attempt succeeded
     */
    public function send(string $recipient, string $subject, string $message): bool;

    /**
     * Return the channel identifier used to pick the right recipient field.
     * Convention: 'email' → use guest's email, 'sms' → use guest's phone.
     *
     * @return string e.g. 'email', 'sms'
     */
    public function getChannel(): string;
}
