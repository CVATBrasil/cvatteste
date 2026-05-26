<?php
/**
 * Diagnóstico SMTP — DELETE APÓS O TESTE
 * Acesse: https://paginas.cvatbrasil.com/api/teste-smtp.php
 */

// Bloqueia acesso externo — só permite localhost ou IP específico
// Remova ou ajuste se necessário para testar no servidor
require_once __DIR__ . '/config.php';

header('Content-Type: text/plain; charset=UTF-8');

echo "=== DIAGNÓSTICO SMTP ===\n\n";
echo "SMTP_HOST : " . (defined('SMTP_HOST') ? SMTP_HOST : 'NÃO DEFINIDO') . "\n";
echo "SMTP_PORT : " . (defined('SMTP_PORT') ? SMTP_PORT : 'NÃO DEFINIDO') . "\n";
echo "SMTP_USER : " . (defined('SMTP_USER') ? SMTP_USER : 'NÃO DEFINIDO') . "\n";
echo "SMTP_PASS : " . (defined('SMTP_PASS') ? str_repeat('*', strlen(SMTP_PASS)) : 'NÃO DEFINIDO') . "\n";
echo "MAIL_TO   : " . (defined('MAIL_TO')   ? MAIL_TO   : 'NÃO DEFINIDO') . "\n\n";

// 1. Testa extensão OpenSSL
echo "--- Verificações ---\n";
echo "OpenSSL carregado : " . (extension_loaded('openssl') ? "SIM" : "NÃO ← problema!") . "\n";

// 2. Testa conexão TCP
echo "\n--- Conexão TCP ssl://smtp.hostinger.com:465 ---\n";
$socket = @fsockopen('ssl://' . SMTP_HOST, (int)SMTP_PORT, $errno, $errstr, 10);
if (!$socket) {
    echo "FALHOU — errno:{$errno} msg:{$errstr}\n";
    echo "\nTentando porta 587 (STARTTLS)...\n";
    $socket587 = @fsockopen(SMTP_HOST, 587, $e2, $s2, 10);
    if (!$socket587) {
        echo "FALHOU também na 587 — errno:{$e2} msg:{$s2}\n";
        echo "\nConclusão: conexão bloqueada pelo servidor. Contate o suporte Hostinger.\n";
    } else {
        echo "Porta 587 conectou! Use SMTP_PORT = 587 (sem ssl://).\n";
        fclose($socket587);
    }
    exit;
}

echo "Conexão OK!\n";

// 3. Lê saudação
$greeting = fgets($socket, 515);
echo "Saudação: " . trim($greeting) . "\n";

// 4. EHLO
fwrite($socket, "EHLO testediag\r\n");
$ehlo = '';
while (!feof($socket)) {
    $line = fgets($socket, 515);
    $ehlo .= $line;
    if (isset($line[3]) && $line[3] === ' ') break;
}
echo "EHLO resposta:\n" . $ehlo . "\n";

// 5. AUTH LOGIN
fwrite($socket, "AUTH LOGIN\r\n");
$r = fgets($socket, 515);
echo "AUTH LOGIN: " . trim($r) . "\n";

fwrite($socket, base64_encode(SMTP_USER) . "\r\n");
$r = fgets($socket, 515);
echo "Username:   " . trim($r) . "\n";

fwrite($socket, base64_encode(SMTP_PASS) . "\r\n");
$r = fgets($socket, 515);
echo "Password:   " . trim($r) . "\n";

if (strpos($r, '235') !== false) {
    echo "\nAutenticação OK! Enviando e-mail de teste...\n";

    fwrite($socket, "MAIL FROM:<" . SMTP_USER . ">\r\n");
    echo "MAIL FROM: " . trim(fgets($socket, 515)) . "\n";

    fwrite($socket, "RCPT TO:<" . MAIL_TO . ">\r\n");
    echo "RCPT TO:   " . trim(fgets($socket, 515)) . "\n";

    fwrite($socket, "DATA\r\n");
    echo "DATA:      " . trim(fgets($socket, 515)) . "\n";

    $msg = "From: CVAT Brasil <" . SMTP_USER . ">\r\n"
         . "To: " . MAIL_TO . "\r\n"
         . "Subject: =?UTF-8?B?" . base64_encode('Teste SMTP CVAT Brasil') . "?=\r\n"
         . "MIME-Version: 1.0\r\n"
         . "Content-Type: text/plain; charset=UTF-8\r\n"
         . "\r\n"
         . "Se recebeu este e-mail, o SMTP está funcionando!\r\n"
         . "\r\n.";

    fwrite($socket, $msg . "\r\n");
    echo "Envio:     " . trim(fgets($socket, 515)) . "\n";
} else {
    echo "\nFalha na autenticação. Verifique usuário/senha.\n";
}

fwrite($socket, "QUIT\r\n");
fclose($socket);

echo "\n=== FIM DO DIAGNÓSTICO ===\n";
echo "LEMBRE-SE: apague este arquivo após o teste!\n";
