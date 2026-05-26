<?php
/**
 * Envio de e-mail via SMTP autenticado (SSL/TLS, porta 465).
 * Usa apenas funções nativas do PHP — sem dependências externas.
 *
 * Requer em config.php:
 *   define('SMTP_HOST', 'smtp.hostinger.com');
 *   define('SMTP_PORT', 465);
 *   define('SMTP_USER', 'cvat@cvatbrasil.com');
 *   define('SMTP_PASS', 'sua-senha');
 */

declare(strict_types=1);

function smtpSend(
    string $to,
    string $subject,
    string $body,
    string $replyTo = ''
): bool {
    if (!defined('SMTP_HOST') || !defined('SMTP_USER') || !defined('SMTP_PASS')) {
        error_log('smtpSend: constantes SMTP não definidas em config.php');
        return false;
    }

    $host = SMTP_HOST;
    $port = defined('SMTP_PORT') ? (int) SMTP_PORT : 465;
    $user = SMTP_USER;
    $pass = SMTP_PASS;

    $socket = @fsockopen('ssl://' . $host, $port, $errno, $errstr, 10);
    if (!$socket) {
        error_log("smtpSend: conexão falhou — {$errno} {$errstr}");
        return false;
    }

    stream_set_timeout($socket, 10);

    /* Lê resposta linha a linha (suporta respostas multi-linha como EHLO) */
    $read = static function () use ($socket): string {
        $out = '';
        while (($line = fgets($socket, 515)) !== false) {
            $out .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $out;
    };

    $read(); // saudação 220

    fwrite($socket, "EHLO localhost\r\n");
    $read(); // 250-... (multi-linha)

    fwrite($socket, "AUTH LOGIN\r\n");
    $read(); // 334 VXNlcm5hbWU6

    fwrite($socket, base64_encode($user) . "\r\n");
    $read(); // 334 UGFzc3dvcmQ6

    fwrite($socket, base64_encode($pass) . "\r\n");
    $authResp = $read(); // 235 Authentication successful

    if (strpos($authResp, '235') === false) {
        error_log('smtpSend: autenticação falhou — ' . trim($authResp));
        fclose($socket);
        return false;
    }

    fwrite($socket, "MAIL FROM:<{$user}>\r\n");
    $read();

    fwrite($socket, "RCPT TO:<{$to}>\r\n");
    $rcptResp = $read();
    if (strpos($rcptResp, '250') === false) {
        error_log('smtpSend: RCPT TO rejeitado — ' . trim($rcptResp));
        fclose($socket);
        return false;
    }

    fwrite($socket, "DATA\r\n");
    $read(); // 354

    $fromName  = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'CVAT Brasil';
    $replyLine = $replyTo !== '' ? "Reply-To: {$replyTo}\r\n" : '';

    $headers = "From: {$fromName} <{$user}>\r\n"
             . "To: {$to}\r\n"
             . $replyLine
             . 'Subject: =?UTF-8?B?' . base64_encode($subject) . "?=\r\n"
             . "MIME-Version: 1.0\r\n"
             . "Content-Type: text/plain; charset=UTF-8\r\n"
             . "\r\n";

    fwrite($socket, $headers . $body . "\r\n.\r\n");
    $sendResp = $read(); // 250 queued

    fwrite($socket, "QUIT\r\n");
    fclose($socket);

    if (strpos($sendResp, '250') === false) {
        error_log('smtpSend: envio não confirmado — ' . trim($sendResp));
        return false;
    }

    return true;
}
