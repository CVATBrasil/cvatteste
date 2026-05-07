<?php
/**
 * GET /api/blog.php
 * Retorna posts publicados: destaque primeiro, depois por data desc.
 * Resposta: array JSON idêntico ao data/blog.json
 *
 * Parâmetros opcionais de querystring:
 *   ?categoria=nr01        filtra por categoria
 *   ?limit=10              limita o número de resultados
 */

declare(strict_types=1);
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    errorResponse('Método não permitido', 405);
}

try {
    $where  = ['publicado = 1'];
    $params = [];

    $cat = trim($_GET['categoria'] ?? '');
    if ($cat !== '') {
        $where[]         = 'categoria = :categoria';
        $params[':categoria'] = $cat;
    }

    $limit = (int) ($_GET['limit'] ?? 0);
    $limitSql = $limit > 0 ? " LIMIT {$limit}" : '';

    $sql = 'SELECT id, destaque, titulo, excerpt, categoria, categoria_label,
                   cor, data_publicacao, tempo_leitura,
                   autor_nome, autor_iniciais, autor_cor, link
            FROM blog_posts
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY destaque DESC, data_publicacao DESC'
           . $limitSql;

    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    foreach ($rows as &$row) {
        $row['id']           = (int) $row['id'];
        $row['destaque']     = (bool) $row['destaque'];
        $row['tempo_leitura'] = (int) $row['tempo_leitura'];
        $row['data_iso']      = $row['data_publicacao'];
        $row['data_formatada'] = formatDatePtBr($row['data_publicacao']);
        unset($row['data_publicacao']);
    }

    jsonResponse($rows);

} catch (PDOException $e) {
    errorResponse('Erro ao carregar artigos', 500);
}
