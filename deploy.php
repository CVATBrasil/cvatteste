<?php
/**
 * Deploy automático — executa git pull origin main
 * Acesse: https://paginas.cvatbrasil.com/deploy.php?key=cvat2026
 */
if (($_GET['key'] ?? '') !== 'cvat2026') {
    http_response_code(403); exit('Acesso negado');
}

header('Content-Type: text/html; charset=UTF-8');

function runCmd(string $cmd): array {
    $output = ''; $code = -1;

    if (function_exists('proc_open')) {
        $desc = [1 => ['pipe','w'], 2 => ['pipe','w']];
        $proc = proc_open($cmd, $desc, $pipes, __DIR__);
        if (is_resource($proc)) {
            $output  = stream_get_contents($pipes[1]);
            $output .= stream_get_contents($pipes[2]);
            fclose($pipes[1]); fclose($pipes[2]);
            $code = proc_close($proc);
        }
    } elseif (function_exists('shell_exec')) {
        $output = shell_exec($cmd . ' 2>&1') ?? '';
        $code   = 0;
    } elseif (function_exists('exec')) {
        $lines = []; exec($cmd . ' 2>&1', $lines, $code);
        $output = implode("\n", $lines);
    } elseif (function_exists('system')) {
        ob_start(); system($cmd . ' 2>&1', $code); $output = ob_get_clean();
    } else {
        $output = 'Nenhuma função de execução disponível no servidor.';
        $code   = -1;
    }

    return ['output' => trim($output), 'code' => $code];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Deploy — CVAT Brasil</title>
<style>
  body { font-family: sans-serif; max-width: 640px; margin: 60px auto; padding: 0 20px; }
  h2   { color: #1e40af; }
  pre  { background: #f1f5f9; border-radius: 8px; padding: 16px; font-size: .85rem; white-space: pre-wrap; word-break: break-all; line-height: 1.6; }
  .ok  { color: #16a34a; font-weight: 700; font-size: 1.1rem; }
  .err { color: #dc2626; font-weight: 700; font-size: 1.1rem; }
  .info{ color: #6b7280; font-size: .85rem; margin-top: 12px; }
</style>
</head>
<body>
<h2>Deploy CVAT Brasil</h2>
<?php

// Funções disponíveis
$available = array_filter(['proc_open','shell_exec','exec','system','passthru'], 'function_exists');
echo '<p class="info">Funções disponíveis: ' . implode(', ', $available) . '</p>';

// git pull
$pull = runCmd('git -C ' . escapeshellarg(__DIR__) . ' pull origin main');

if ($pull['code'] === 0) {
    echo '<p class="ok">✅ Deploy realizado com sucesso!</p>';
} else {
    echo '<p class="err">❌ Erro no deploy (código ' . $pull['code'] . ')</p>';
}

echo '<pre>' . htmlspecialchars($pull['output'] ?: '(sem saída)') . '</pre>';

// último commit
$log = runCmd('git -C ' . escapeshellarg(__DIR__) . ' log -1 --pretty=format:"%h — %s (%ar)"');
if ($log['output']) {
    echo '<p><strong>Último commit:</strong> ' . htmlspecialchars($log['output']) . '</p>';
}

echo '<p class="info">Acesse novamente para puxar novas atualizações.</p>';
?>
</body>
</html>
