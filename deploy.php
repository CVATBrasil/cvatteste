<?php
/**
 * Deploy automático — executa git pull origin main
 * Acesse: https://paginas.cvatbrasil.com/deploy.php?key=cvat2026
 */
if (($_GET['key'] ?? '') !== 'cvat2026') {
    http_response_code(403); exit('Acesso negado');
}

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Deploy — CVAT Brasil</title>
<style>
  body { font-family: sans-serif; max-width: 600px; margin: 60px auto; padding: 0 20px; }
  h2   { color: #1e40af; }
  pre  { background: #f1f5f9; border-radius: 8px; padding: 16px; font-size: .9rem; white-space: pre-wrap; word-break: break-all; }
  .ok  { color: #16a34a; font-weight: 700; }
  .err { color: #dc2626; font-weight: 700; }
</style>
</head>
<body>
<h2>Deploy CVAT Brasil</h2>
<?php

$repoPath = __DIR__;

// Garante que estamos no repositório correto
chdir($repoPath);

// Executa git pull
$output = [];
$return = 0;
exec('git pull origin main 2>&1', $output, $return);

$texto = implode("\n", $output);

if ($return === 0) {
    echo '<p class="ok">✅ Deploy realizado com sucesso!</p>';
} else {
    echo '<p class="err">❌ Erro no deploy (código ' . $return . ')</p>';
}

echo '<pre>' . htmlspecialchars($texto) . '</pre>';

// Mostra o último commit aplicado
$commit = shell_exec('git log -1 --pretty=format:"%h — %s (%ar)" 2>&1');
echo '<p><strong>Último commit:</strong> ' . htmlspecialchars($commit) . '</p>';

echo '<p style="color:#6b7280;font-size:.85rem">Execute novamente se precisar puxar mais atualizações.</p>';
?>
</body>
</html>
