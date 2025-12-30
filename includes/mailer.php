<?php
// includes/mailer.php
// Provides send_mail($to, $subject, $body, $isHtml=false)
// Tries to use PHPMailer (vendor/autoload.php) if available and config says so.
require_once __DIR__ . '/mailer_config.php';
$config = include __DIR__ . '/mailer_config.php';

function send_mail(string $to, string $subject, string $body, bool $isHtml = false): bool {
    global $config;
    // Try PHPMailer if available and configured
    if (!empty($config['use_smtp'])) {
        if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
            require_once __DIR__ . '/../vendor/autoload.php';
            try {
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                if (!empty($config['smtp'])) {
                    $smtp = $config['smtp'];
                    $mail->isSMTP();
                    $mail->Host = $smtp['host'];
                    $mail->Port = $smtp['port'];
                    $mail->SMTPAuth = true;
                    $mail->Username = $smtp['username'];
                    $mail->Password = $smtp['password'];
                    if (!empty($smtp['secure'])) $mail->SMTPSecure = $smtp['secure'];
                }
                $from = $config['from_email'] ?? 'no-reply@localhost';
                $fromName = $config['from_name'] ?? 'No Reply';
                $mail->setFrom($from, $fromName);
                $mail->addAddress($to);
                $mail->Subject = $subject;
                if ($isHtml) $mail->isHTML(true);
                $mail->Body = $body;
                $mail->send();
                return true;
            } catch (Exception $e) {
                // fallback to mail()
            }
        }
    }

    // Fallback to PHP mail()
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $headers = "From: " . ($config['from_email'] ?? 'no-reply@' . $host) . "\r\n";
    if ($isHtml) $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    else $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    return @mail($to, $subject, $body, $headers);
}
