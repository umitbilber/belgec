<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;
use App\Interfaces\MailServiceInterface;
use App\Interfaces\SettingsServiceInterface;

class MailService implements MailServiceInterface
{
    private SettingsServiceInterface $settingsService;

public function __construct(SettingsServiceInterface $settingsService)
{
    $this->settingsService = $settingsService;
}

    private function loadPhpMailer(): void
    {
        require_once BASE_PATH . '/phpmailer/PHPMailer.php';
        require_once BASE_PATH . '/phpmailer/SMTP.php';
        require_once BASE_PATH . '/phpmailer/Exception.php';
    }

    public function sendHtml(string $to, string $subject, string $htmlBody, ?string $replyTo = null): void
    {
        $ayarlar = $this->settingsService->all();

        $smtpHost = trim((string) ($ayarlar['smtp_host'] ?? ''));
        $smtpMail = trim((string) ($ayarlar['smtp_mail'] ?? ''));
        $smtpSifre = (string) ($ayarlar['smtp_sifre'] ?? '');
        $fromName = trim((string) ($ayarlar['sirket_adi'] ?? 'Belgeç'));

        if ($smtpHost === '' || $smtpMail === '') {
            throw new RuntimeException('SMTP ayarları eksik. Ayarlar ekranından SMTP bilgilerini kontrol edin.');
        }

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Geçerli bir alıcı e-posta adresi bulunamadı.');
        }

        if (!filter_var($smtpMail, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('SMTP gönderen e-posta adresi geçersiz.');
        }

        if ($replyTo !== null && $replyTo !== '' && !filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Yanıt adresi geçersiz.');
        }

        $this->loadPhpMailer();

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpMail;
            $mail->Password = $smtpSifre;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($smtpMail, $fromName);
            $mail->addAddress($to);

            if ($replyTo) {
                $mail->addReplyTo($replyTo, $fromName);
            } else {
                $mail->addReplyTo($smtpMail, $fromName);
            }

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = trim(html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody)), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            $mail->send();
        } catch (Exception $e) {
            throw new RuntimeException('Mail gönderilemedi: ' . ($mail->ErrorInfo ?: $e->getMessage()));
        }
    }
}