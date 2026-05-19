<?php
/**
 * POST /api/contato.php
 * Salva a submissão do formulário de contato no banco e envia e-mail de notificação.
 * Aceita JSON (fetch) ou form-data tradicional.
 *
 * Campos esperados: nome, email, empresa, cargo, telefone, assunto, mensagem
 */

declare(strict_types=1);
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Método não permitido', 405);
}

/* ── Lê o payload ── */
$raw  = file_get_contents('php://input');
$data = $raw ? (json_decode($raw, true) ?? []) : $_POST;

$nome     = trim((string) ($data['name']     ?? $data['nome']     ?? ''));
$email    = trim((string) ($data['email']    ?? ''));
$empresa  = trim((string) ($data['empresa']  ?? $data['company']  ?? ''));
$cargo    = trim((string) ($data['cargo']    ?? $data['role']     ?? ''));
$telefone = trim((string) ($data['telefone'] ?? $data['phone']    ?? ''));
$assunto  = trim((string) ($data['assunto']  ?? $data['subject']  ?? ''));
$mensagem = trim((string) ($data['mensagem'] ?? $data['message']  ?? ''));

/* ── Validação ── */
if ($nome === '') {
    errorResponse('O campo nome é obrigatório.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    errorResponse('Informe um e-mail válido.');
}
if (mb_strlen($mensagem) > 5000) {
    errorResponse('Mensagem muito longa (máx. 5000 caracteres).');
}

/* ── Persistência ── */
try {
    $stmt = getDB()->prepare(
        'INSERT INTO contatos (nome, email, empresa, cargo, telefone, assunto, mensagem, ip)
         VALUES (:nome, :email, :empresa, :cargo, :telefone, :assunto, :mensagem, :ip)'
    );
    $stmt->execute([
        ':nome'     => $nome,
        ':email'    => $email,
        ':empresa'  => $empresa  ?: null,
        ':cargo'    => $cargo    ?: null,
        ':telefone' => $telefone ?: null,
        ':assunto'  => $assunto  ?: null,
        ':mensagem' => $mensagem ?: null,
        ':ip'       => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);
} catch (PDOException $e) {
    errorResponse('Erro ao salvar mensagem. Tente novamente.', 500);
}

/* ── Notificação por e-mail (requer MAIL_TO definido em config.php) ── */
if (defined('MAIL_TO') && MAIL_TO !== '') {
    $subject = $assunto ?: 'Nova mensagem de contato — CVAT Brasil';
    $body    = "Nome: {$nome}\n"
             . "E-mail: {$email}\n"
             . ($empresa  ? "Empresa: {$empresa}\n"  : '')
             . ($cargo    ? "Cargo: {$cargo}\n"      : '')
             . ($telefone ? "Telefone: {$telefone}\n": '')
             . "\n--- Mensagem ---\n{$mensagem}";

    $headers = implode("\r\n", [
        'From: ' . MAIL_FROM,
        'Reply-To: ' . $email,
        'Content-Type: text/plain; charset=UTF-8',
        'X-Mailer: CVAT Brasil / PHP',
    ]);

    @mail(MAIL_TO, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
}

jsonResponse(['ok' => true, 'message' => 'Mensagem recebida! Retornaremos em até 24h.']);
