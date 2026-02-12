<?php

namespace App\Services\Email;

interface EmailProviderInterface
{
    /**
     * Send transactional email via API.
     *
     * @param string $recipient
     * @param string $subject
     * @param string $content (HTML or Text)
     * @param string|null $fromName
     * @param string|null $fromEmail
     * @param array $attachments (optional)
     * @return array Response from provider
     */
    public function sendEmail(string $recipient, string $subject, string $content, ?string $fromName = null, ?string $fromEmail = null, array $attachments = []): array;

    /**
     * Get all mailing lists from the account.
     *
     * @return array List of mailing lists
     */
    public function getAllLists(): array;

    /**
     * Add subscriber to a specific mailing list.
     *
     * @param string $listId
     * @param string $email
     * @param array $additionalData (e.g., first_name, last_name, etc.)
     * @return array Response from provider
     */
    public function addSubscriber(string $listId, string $email, array $additionalData = []): array;
}
