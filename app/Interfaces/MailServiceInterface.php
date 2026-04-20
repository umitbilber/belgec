<?php

declare(strict_types=1);

namespace App\Interfaces;

interface MailServiceInterface
{
    public function sendHtml(string $to, string $subject, string $htmlBody, ?string $replyTo = null): void;
}