<?php
/**
 * Patch: adiciona MEC às categorias e inverte ordem de exibição
 * Acesse: /api/patch-mec-ordem.php?key=cvat2026
 * APAGUE após executar.
 */
if (($_GET['key'] ?? '') !== 'cvat2026') { http_response_code(403); exit('Acesso negado'); }
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/config.php';
$pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Patch</title>
<style>body{font-family:sans-serif;max-width:700px;margin:40px auto;padding:0 20px}
.ok{color:#059669;font-weight:700}.err{color:#dc2626;font-weight:700}
table{width:100%;border-collapse:collapse;margin:12px 0}
th,td{padding:8px 12px;border:1px solid #e5e7eb;font-size:.85rem}th{background:#f8fafc}</style>
</head><body><h2>Patch — MEC + Ordem Decrescente</h2><table>
<tr><th>ID</th><th>Título</th><th>Categorias</th><th>Ordem</th><th>Status</th></tr>';

// [id => [categorias, ordem_nova]]
$updates = [
    4 => ['["CVAT","MEC"]',  6],
    5 => ['["NR-01","MEC"]', 5],
    8 => ['["NR-01","MEC"]', 2],
    9 => ['["NR-01","MEC"]', 1],
    // ordem decrescente: menor ordem = aparece primeiro
    1 => ['["CVAT"]',        9],
    2 => ['["CVAT"]',        8],
    3 => ['["CVAT"]',        7],
    6 => ['["NR-01","CVAT"]',4],
    7 => ['["NR-01"]',       3],
];

$stmtGet = $pdo->prepare("SELECT titulo FROM treinamentos WHERE id=?");
$stmtUpd = $pdo->prepare("UPDATE treinamentos SET categorias=?, ordem=? WHERE id=?");

foreach ($updates as $id => [$cats, $ordem]) {
    try {
        $stmtGet->execute([$id]);
        $titulo = $stmtGet->fetchColumn() ?: "ID $id";
        $stmtUpd->execute([$cats, $ordem, $id]);
        echo "<tr><td>$id</td><td>$titulo</td><td><code>$cats</code></td><td>$ordem</td><td class='ok'>✅ OK</td></tr>";
    } catch (PDOException $e) {
        echo "<tr><td>$id</td><td>—</td><td>$cats</td><td>$ordem</td><td class='err'>❌ "
            . htmlspecialchars($e->getMessage()) . "</td></tr>";
    }
}

echo '</table><p class="ok" style="font-size:1.1rem;margin-top:20px">✅ Patch concluído!</p>';
echo '<p style="color:#6b7280;font-size:.85rem">⚠️ Apague este arquivo do servidor após verificar.</p>';
echo '</body></html>';
