<?php

namespace App\Mail\Transport;

use App\Services\Email\MailketingProvider;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;

class MailketingTransport extends AbstractTransport
{
    protected MailketingProvider $provider;

    public function __construct(MailketingProvider $provider, EventDispatcherInterface $dispatcher = null, LoggerInterface $logger = null)
    {
        parent::__construct($dispatcher, $logger);
        $this->provider = $provider;
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        
        $from = $email->getFrom()[0];
        $fromName = $from->getName();
        $fromEmail = $from->getAddress();

        $to = [];
        foreach ($email->getTo() as $address) {
            $to[] = $address->getAddress();
        }
        
        // Mailketing API only supports single recipient per call in the example provided?
        // The example says 'recipient' : 'address'. It doesn't show array support.
        // We will loop if multiple recipients, or just take the first one if strict.
        // For transactional emails, usually it's one recipient.
        
        $subject = $email->getSubject();
        $content = $email->getHtmlBody() ?: $email->getTextBody();
        
        // Handle attachments
        $attachments = [];
        foreach ($email->getAttachments() as $attachment) {
            // Mailketing expects URL for attachments in the example.
            // If we have raw content, we might need to upload it or skip it.
            // For now, let's assume we can't easily support raw attachments without an upload step 
            // unless Mailketing supports base64 which isn't in the provided docs.
            // We'll log a warning if attachments are present.
            if ($this->logger) {
                $this->logger->warning("MailketingTransport: Attachment '{$attachment->getName()}' skipped as API requires URL.");
            }
        }

        foreach ($to as $recipient) {
            $result = $this->provider->sendEmail(
                $recipient,
                $subject,
                $content,
                $fromName,
                $fromEmail,
                $attachments
            );

            if (!$result['success']) {
                throw new \Exception("Mailketing Error: " . $result['message']);
            }
        }
    }

    public function __toString(): string
    {
        return 'mailketing';
    }
}
