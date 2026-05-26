<?php
/**
 * CVAT Brasil — Teste de conexão com o banco de dados
 *
 * IMPORTANTE: Apague este arquivo após confirmar que tudo funciona.
 * Acesse: https://seudominio.com.br/teste-db.php
 */

// Bloqueia acesso externo por IP (opcional — remova se quiser testar de qualquer lugar)
// $allowedIPs = ['SEU_IP_AQUI'];
// if (!in_array($_SERVER['REMOTE_ADDR'], $allowedIPs)) { http_response_code(403); exit; }

require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/api/db.php';

$results = [];
$allOk   = true;

/* ── 1. Conexão com o banco ── */
try {
    $pdo = getDB();
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    $results[] = ['ok' => true,  'label' => 'Conexão MySQL', 'value' => $version];
} catch (PDOException $e) {
    $results[] = ['ok' => false, 'label' => 'Conexão MySQL', 'value' => $e->getMessage()];
    $allOk = false;
}

/* ── 2. Tabelas existentes ── */
$tabelas = ['treinamentos', 'blog_posts', 'contatos', 'diagnosticos', 'leads'];
foreach ($tabelas as $tabela) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM `{$tabela}`")->fetchColumn();
        $results[] = ['ok' => true, 'label' => "Tabela: {$tabela}", 'value' => "{$count} registro(s)"];
    } catch (PDOException $e) {
        $results[] = ['ok' => false, 'label' => "Tabela: {$tabela}", 'value' => 'Não encontrada — rode o schema.sql'];
        $allOk = false;
    }
}

/* ── 3. API treinamentos ── */
try {
    $rows = $pdo->query('SELECT titulo FROM treinamentos WHERE ativo = 1 LIMIT 1')->fetchAll();
    $results[] = ['ok' => true, 'label' => 'API Treinamentos', 'value' => $rows ? $rows[0]['titulo'] : 'Sem dados — rode o seed.sql'];
} catch (PDOException $e) {
    $results[] = ['ok' => false, 'label' => 'API Treinamentos', 'value' => $e->getMessage()];
    $allOk = false;
}

/* ── 4. API blog ── */
try {
    $rows = $pdo->query('SELECT titulo FROM blog_posts WHERE publicado = 1 LIMIT 1')->fetchAll();
    $results[] = ['ok' => true, 'label' => 'API Blog', 'value' => $rows ? $rows[0]['titulo'] : 'Sem dados — rode o seed.sql'];
} catch (PDOException $e) {
    $results[] = ['ok' => false, 'label' => 'API Blog', 'value' => $e->getMessage()];
    $allOk = false;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Teste de Banco — CVAT Brasil</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: system-ui, sans-serif; background: #f4f6f9; padding: 2rem; color: #1a1a2e; }
    .card { background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.08); max-width: 640px; margin: 0 auto; overflow: hidden; }
    .card-header { padding: 1.5rem 2rem; background: <?= $allOk ? '#011633' : '#b91c1c' ?>; color: white; }
    .card-header h1 { font-size: 1.25rem; font-weight: 700; }
    .card-header p  { font-size: .875rem; opacity: .8; margin-top: .25rem; }
    .card-body { padding: 1.5rem 2rem; }
    .item { display: flex; align-items: flex-start; gap: 1rem; padding: .75rem 0; border-bottom: 1px solid #f0f0f0; }
    .item:last-child { border-bottom: none; }
    .icon { width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: .8rem; font-weight: 700; margin-top: 1px; }
    .icon-ok  { background: #d1fae5; color: #065f46; }
    .icon-err { background: #fee2e2; color: #991b1b; }
    .label { font-weight: 600; font-size: .9rem; }
    .value { font-size: .825rem; color: #6b7280; margin-top: 2px; word-break: break-all; }
    .badge { display: inline-block; padding: .2rem .75rem; border-radius: 99px; font-size: .75rem; font-weight: 700; margin-top: 1rem; }
    .badge-ok  { background: #d1fae5; color: #065f46; }
    .badge-err { background: #fee2e2; color: #991b1b; }
    .warning { margin-top: 1.5rem; padding: 1rem; background: #fef3c7; border-radius: 8px; font-size: .8rem; color: #92400e; border-left: 4px solid #f59e0b; }
  </style>
</head>
<body>
  <div class="card">
    <div class="card-header">
      <h1>CVAT Brasil — Diagnóstico de Banco de Dados</h1>
      <p>Banco: <strong><?= DB_NAME ?></strong> · Host: <strong><?= DB_HOST ?></strong></p>
    </div>
    <div class="card-body">

      <?php foreach ($results as $r): ?>
        <div class="item">
          <div class="icon <?= $r['ok'] ? 'icon-ok' : 'icon-err' ?>">
            <?= $r['ok'] ? '✓' : '✗' ?>
          </div>
          <div>
            <div class="label"><?= htmlspecialchars($r['label']) ?></div>
            <div class="value"><?= htmlspecialchars($r['value']) ?></div>
          </div>
        </div>
      <?php endforeach; ?>

      <div>
        <span class="badge <?= $allOk ? 'badge-ok' : 'badge-err' ?>">
          <?= $allOk ? '✓ Tudo funcionando corretamente' : '✗ Há itens que precisam de atenção' ?>
        </span>
      </div>

      <div class="warning">
        ⚠️ <strong>Apague este arquivo após os testes.</strong><br>
        O arquivo <code>teste-db.php</code> expõe informações do banco — remova-o assim que confirmar que tudo está funcionando.
      </div>

    </div>
  </div>
</body>
</html>
