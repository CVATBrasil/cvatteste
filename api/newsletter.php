<?php
/**
 * POST /api/newsletter.php
 * Cadastra um e-mail na lista de leads.
 * Se o e-mail já existir, reativa o cadastro (ON DUPLICATE KEY UPDATE).
 *
 * Campos esperados: email, fonte (opcional — default: 'newsletter')
 */

declare(strict_types=1);
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Método não permitido', 405);
}

/* ── Lê o payload ── */
$raw  = file_get_contents('php://input');
$data = $raw ? (json_decode($raw, true) ?? []) : $_POST;

$email = trim((string) ($data['email'] ?? ''));
$fonte = trim((string) ($data['fonte'] ?? 'newsletter'));

/* ── Validação ── */
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    errorResponse('Informe um e-mail válido.');
}

/* ── Persistência ── */
try {
    $stmt = getDB()->prepare(
        'INSERT INTO leads (email, fonte)
         VALUES (:email, :fonte)
         ON DUPLICATE KEY UPDATE ativo = 1, fonte = VALUES(fonte)'
    );
    $stmt->execute([
        ':email' => $email,
        ':fonte' => $fonte ?: 'newsletter',
    ]);
} catch (PDOException $e) {
    errorResponse('Erro ao cadastrar. Tente novamente.', 500);
}

jsonResponse(['ok' => true, 'message' => 'Cadastro realizado com sucesso!']);
