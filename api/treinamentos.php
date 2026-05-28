<?php
/**
 * GET /api/treinamentos.php
 * Retorna todos os treinamentos ativos ordenados por campo `ordem`.
 * Resposta: array JSON idêntico ao data/treinamentos.json
 */

declare(strict_types=1);
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    errorResponse('Método não permitido', 405);
}

try {
    $stmt = getDB()->query(
        'SELECT id, titulo, descricao, modalidade, cor,
                categorias, publico, topicos, cta_texto, cta_link, pagina_url
         FROM treinamentos
         WHERE ativo = 1
         ORDER BY ordem DESC, id DESC'
    );

    $rows = $stmt->fetchAll();

    foreach ($rows as &$row) {
        $row['id']         = (int) $row['id'];
        $row['categorias'] = json_decode($row['categorias'], true) ?? [];
        $row['topicos']    = json_decode($row['topicos'], true) ?? [];
        $row['pagina_url'] = $row['pagina_url'] ?: null;
    }

    jsonResponse($rows);

} catch (PDOException $e) {
    errorResponse('Erro ao carregar treinamentos', 500);
}
