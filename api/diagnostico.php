<?php
/**
 * POST /api/diagnostico.php
 * Salva a solicitação de diagnóstico gratuito no banco.
 * Campos enviados pelo forms.js: nome, email, telefone, cargo,
 * empresa, setor, colaboradores, interesse, desafio, lgpd, _gotcha
 */

declare(strict_types=1);
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Método não permitido', 405);
}

/* ── Lê o payload ── */
$raw  = file_get_contents('php://input');
$data = $raw ? (json_decode($raw, true) ?? []) : $_POST;

/* Ignore honeypot */
if (!empty($data['_gotcha'])) {
    jsonResponse(['ok' => true]);
}

$nome          = trim((string) ($data['nome']          ?? ''));
$email         = trim((string) ($data['email']         ?? ''));
$telefone      = trim((string) ($data['telefone']      ?? ''));
$cargo         = trim((string) ($data['cargo']         ?? ''));
$empresa       = trim((string) ($data['empresa']       ?? ''));
$setor         = trim((string) ($data['setor']         ?? ''));
$colaboradores = trim((string) ($data['colaboradores'] ?? ''));
$interesse     = trim((string) ($data['interesse']     ?? ''));
$desafio       = trim((string) ($data['desafio']       ?? ''));

/* ── Validação ── */
if ($nome === '' || mb_strlen($nome) < 3) {
    errorResponse('Nome inválido.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    errorResponse('E-mail inválido.');
}
if ($telefone === '') {
    errorResponse('Telefone obrigatório.');
}
if ($cargo === '' || $empresa === '' || $setor === '' || $colaboradores === '') {
    errorResponse('Preencha todos os campos obrigatórios.');
}

$interessesValidos = ['clima', 'nr01', 'saude-mental', 'tudo'];
if (!in_array($interesse, $interessesValidos, true)) {
    errorResponse('Interesse inválido.');
}

/* ── Persistência ── */
try {
    $stmt = getDB()->prepare(
        'INSERT INTO diagnosticos
           (nome, email, telefone, cargo, empresa, setor, colaboradores, interesse, desafio, ip)
         VALUES
           (:nome, :email, :telefone, :cargo, :empresa, :setor, :colaboradores, :interesse, :desafio, :ip)'
    );
    $stmt->execute([
        ':nome'          => $nome,
        ':email'         => $email,
        ':telefone'      => $telefone,
        ':cargo'         => $cargo,
        ':empresa'       => $empresa,
        ':setor'         => $setor,
        ':colaboradores' => $colaboradores,
        ':interesse'     => $interesse,
        ':desafio'       => $desafio ?: null,
        ':ip'            => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);
} catch (PDOException $e) {
    errorResponse('Erro ao salvar solicitação. Tente novamente.', 500);
}

/* ── Notificação por e-mail ── */
if (defined('MAIL_TO') && MAIL_TO !== '') {
    $subject = 'Nova solicitação de diagnóstico gratuito — CVAT Brasil';
    $body    = "Nome: {$nome}\n"
             . "E-mail: {$email}\n"
             . "Telefone: {$telefone}\n"
             . "Cargo: {$cargo}\n"
             . "Empresa: {$empresa}\n"
             . "Setor: {$setor}\n"
             . "Colaboradores: {$colaboradores}\n"
             . "Interesse: {$interesse}\n"
             . ($desafio ? "\nDesafio:\n{$desafio}" : '');

    $headers = implode("\r\n", [
        'From: ' . MAIL_FROM,
        'Reply-To: ' . $email,
        'Content-Type: text/plain; charset=UTF-8',
        'X-Mailer: CVAT Brasil / PHP',
    ]);

    @mail(MAIL_TO, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
}

jsonResponse(['ok' => true, 'message' => 'Solicitação recebida! Você receberá o diagnóstico em até 48h.']);
