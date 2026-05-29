<?php
/**
 * Helper de conexão PDO e utilitários de resposta HTTP.
 * Todos os endpoints da API incluem este arquivo.
 */

declare(strict_types=1);

$configPath = __DIR__ . '/config.php';
if (!file_exists($configPath)) {
    http_response_code(503);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'API não configurada. Crie api/config.php a partir de api/config.example.php.']);
    exit;
}
require_once $configPath;

/* ── Conexão singleton ── */

function getDB(): PDO
{
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST, DB_NAME, $charset
    );
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    return $pdo;
}

/* ── Helpers de resposta ── */

function setCorsHeaders(): void
{
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Accept');
}

function jsonResponse($data, int $status = 200): void
{
    setCorsHeaders();
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function errorResponse(string $message, int $status = 400): void
{
    jsonResponse(['error' => $message], $status);
}

/* ── Preflight CORS ── */

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    setCorsHeaders();
    http_response_code(204);
    exit;
}

/* ── Formatação de data em português ── */

function formatDatePtBr(string $ymd): string
{
    static $meses = [
        '01' => 'jan', '02' => 'fev', '03' => 'mar', '04' => 'abr',
        '05' => 'mai', '06' => 'jun', '07' => 'jul', '08' => 'ago',
        '09' => 'set', '10' => 'out', '11' => 'nov', '12' => 'dez',
    ];
    [$ano, $mes, $dia] = explode('-', $ymd);
    return ltrim($dia, '0') . ' ' . ($meses[$mes] ?? $mes) . ' ' . $ano;
}
