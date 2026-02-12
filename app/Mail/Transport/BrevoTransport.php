<?php

namespace App\Mail\Transport;

use App\Services\Email\BrevoProvider;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;

class BrevoTransport extends AbstractTransport
{
    protected BrevoProvider $provider;

    public function __construct(BrevoProvider $provider, EventDispatcherInterface $dispatcher = null, LoggerInterface $logger = null)
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
        
        $subject = $email->getSubject();
        $content = $email->getHtmlBody() ?: $email->getTextBody();
        
        // Handle attachments if supported by Provider, otherwise log warning
        // Brevo API supports attachmentUrl or content. Provider interface needs to support it.
        // Current BrevoProvider::sendEmail implementation needs to be checked if it supports attachments.
        // For now, we focus on the reset password link which has no attachments.

        foreach ($to as $recipient) {
            $result = $this->provider->sendEmail(
                $recipient,
                $subject,
                $content,
                $fromName,
                $fromEmail
            );

            if (!$result['success']) {
                throw new \Exception("Brevo API Error: " . ($result['message'] ?? 'Unknown error'));
            }
        }
    }

    public function __toString(): string
    {
        return 'brevo';
    }
}
