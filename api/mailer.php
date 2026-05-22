<?php
/**
 * Envio de e-mail via SMTP autenticado (SSL/TLS, porta 465).
 * Usa apenas funções nativas do PHP — sem dependências externas.
 *
 * Configuração em config.php:
 *   define('SMTP_HOST', 'smtp.hostinger.com');
 *   define('SMTP_PORT', 465);
 *   define('SMTP_USER', 'cvat@cvatbrasil.com.br');
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
        return false;
    }

    $host = SMTP_HOST;
    $port = defined('SMTP_PORT') ? (int) SMTP_PORT : 465;
    $user = SMTP_USER;
    $pass = SMTP_PASS;

    $socket = @fsockopen('ssl://' . $host, $port, $errno, $errstr, 10);
    if (!$socket) {
        error_log("SMTP connect failed: {$errno} {$errstr}");
        return false;
    }

    $read = static function () use ($socket): string {
        $data = '';
        while (!feof($socket)) {
            $line = fgets($socket, 515);
            if ($line === false) break;
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $data;
    };

    $send = static function (string $cmd) use ($socket): void {
        fwrite($socket, $cmd . "\r\n");
    };

    $read(); // saudação do servidor

    $send('EHLO ' . (gethostname() ?: 'localhost'));
    $read();

    $send('AUTH LOGIN');
    $read();

    $send(base64_encode($user));
    $read();

    $send(base64_encode($pass));
    $resp = $read();
    if (strpos($resp, '235') === false) {
        error_log('SMTP auth failed: ' . $resp);
        fclose($socket);
        return false;
    }

    $send('MAIL FROM:<' . $user . '>');
    $read();

    $send('RCPT TO:<' . $to . '>');
    $read();

    $send('DATA');
    $read();

    $fromName  = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'CVAT Brasil';
    $replyLine = $replyTo ? "Reply-To: {$replyTo}\r\n" : '';

    $message = "From: {$fromName} <{$user}>\r\n"
             . "To: {$to}\r\n"
             . $replyLine
             . 'Subject: =?UTF-8?B?' . base64_encode($subject) . "?=\r\n"
             . "MIME-Version: 1.0\r\n"
             . "Content-Type: text/plain; charset=UTF-8\r\n"
             . "Content-Transfer-Encoding: base64\r\n"
             . "\r\n"
             . chunk_split(base64_encode($body))
             . "\r\n.";

    $send($message);
    $read();

    $send('QUIT');
    fclose($socket);

    return true;
}
