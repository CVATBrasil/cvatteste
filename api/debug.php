<?php
/**
 * Diagnóstico completo — APAGUE APÓS O TESTE
 * Acesse: https://seusite.com/api/debug.php?key=cvat2026
 */
if (($_GET['key'] ?? '') !== 'cvat2026') {
    http_response_code(403);
    exit('Acesso negado');
}

header('Content-Type: text/plain; charset=UTF-8');
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "=== DIAGNÓSTICO CVAT Brasil ===\n\n";

// 1. Versão do PHP
echo "PHP: " . PHP_VERSION . "\n";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? 'OK' : 'NÃO CARREGADO ← problema!') . "\n";
echo "OpenSSL:   " . (extension_loaded('openssl')   ? 'OK' : 'NÃO CARREGADO') . "\n\n";

// 2. Config
$configPath = __DIR__ . '/config.php';
echo "config.php: " . (file_exists($configPath) ? 'EXISTE' : 'NÃO ENCONTRADO ← problema!') . "\n";
if (!file_exists($configPath)) exit;

require_once $configPath;

echo "DB_HOST:   " . (defined('DB_HOST')   ? DB_HOST   : 'não definido') . "\n";
echo "DB_NAME:   " . (defined('DB_NAME')   ? DB_NAME   : 'não definido') . "\n";
echo "DB_USER:   " . (defined('DB_USER')   ? DB_USER   : 'não definido') . "\n";
echo "DB_PASS:   " . (defined('DB_PASS')   ? str_repeat('*', strlen(DB_PASS)) : 'não definido') . "\n\n";

// 3. Conexão DB
echo "--- Banco de Dados ---\n";
try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "Conexão: OK\n";

    // Verifica tabelas
    $tabelas = ['contatos', 'diagnosticos', 'leads'];
    foreach ($tabelas as $t) {
        $r = $pdo->query("SHOW TABLES LIKE '$t'")->rowCount();
        echo "Tabela '$t': " . ($r ? 'EXISTE' : 'NÃO EXISTE ← rode schema.sql!') . "\n";
    }
} catch (PDOException $e) {
    echo "FALHOU: " . $e->getMessage() . "\n";
}

// 4. Teste SMTP
echo "\n--- SMTP ---\n";
echo "SMTP_HOST: " . (defined('SMTP_HOST') ? SMTP_HOST : 'não definido') . "\n";
echo "SMTP_PORT: " . (defined('SMTP_PORT') ? SMTP_PORT : 'não definido') . "\n";
echo "SMTP_USER: " . (defined('SMTP_USER') ? SMTP_USER : 'não definido') . "\n";

if (defined('SMTP_HOST') && defined('SMTP_PORT')) {
    $sock = @fsockopen('ssl://' . SMTP_HOST, (int)SMTP_PORT, $errno, $errstr, 8);
    if ($sock) {
        echo "Conexão SSL porta " . SMTP_PORT . ": OK\n";
        fclose($sock);
    } else {
        echo "Conexão SSL porta " . SMTP_PORT . ": FALHOU ($errno: $errstr)\n";
        // testa 587
        $sock2 = @fsockopen(SMTP_HOST, 587, $e2, $s2, 8);
        if ($sock2) {
            echo "Porta 587 (sem SSL): DISPONÍVEL — considere usar porta 587\n";
            fclose($sock2);
        }
    }
}

// 5. Simula POST no contato.php
echo "\n--- Simulação de INSERT ---\n";
try {
    $pdo2 = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $stmt = $pdo2->prepare(
        'INSERT INTO contatos (nome, email, mensagem, ip) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute(['Teste Debug', 'debug@teste.com', 'Teste automático do debug.php', '127.0.0.1']);
    $id = $pdo2->lastInsertId();
    echo "INSERT contatos: OK (id=$id)\n";
    // limpa o registro de teste
    $pdo2->exec("DELETE FROM contatos WHERE id=$id");
    echo "Limpeza: OK\n";
} catch (Throwable $e) {
    echo "INSERT FALHOU: " . $e->getMessage() . "\n";
}

echo "\n=== FIM — apague este arquivo! ===\n";
