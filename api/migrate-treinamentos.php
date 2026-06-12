<?php
/**
 * Migração de treinamentos — substitui dados fictícios pelos reais
 * Acesse: /api/migrate-treinamentos.php?key=cvat2026
 * APAGUE este arquivo do servidor após rodar com sucesso.
 */
if (($_GET['key'] ?? '') !== 'cvat2026') {
    http_response_code(403); exit('Acesso negado');
}

header('Content-Type: text/html; charset=UTF-8');

$configPath = __DIR__ . '/config.php';
if (!file_exists($configPath)) { die('<p style="color:red">❌ api/config.php não encontrado.</p>'); }
require_once $configPath;

$pdo = new PDO(
    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
    DB_USER, DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8">
<title>Migração — CVAT Brasil</title>
<style>
body{font-family:sans-serif;max-width:800px;margin:40px auto;padding:0 20px}
h2{color:#1e40af} h3{color:#374151;margin:20px 0 8px}
.ok{color:#059669;font-weight:700} .err{color:#dc2626;font-weight:700}
table{width:100%;border-collapse:collapse;margin:12px 0}
th,td{padding:8px 12px;border:1px solid #e5e7eb;font-size:.85rem;text-align:left}
th{background:#f8fafc;font-weight:700}
</style></head><body>
<h2>Migração de Treinamentos — CVAT Brasil</h2>';

// ── Garante coluna pagina_url ─────────────────────────────────
try { $pdo->exec("ALTER TABLE treinamentos ADD COLUMN `pagina_url` VARCHAR(500) DEFAULT NULL AFTER `cta_link`"); }
catch (PDOException) { /* já existe */ }

// ── Dados reais (do arquivo cursos_cvat_estrutura.xlsx) ────────
$cursos = [
  [
    'id'         => 1,
    'titulo'     => 'Especialista em Valores Pessoais CVAT',
    'descricao'  => 'Formação completa sobre valores pessoais aplicados ao comportamento humano, tomada de decisão, carreira, relacionamentos e desenvolvimento profissional utilizando a metodologia CVAT.',
    'modalidade' => 'EAD (assíncrono)',
    'cor'        => 'blue',
    'categorias' => '["CVAT"]',
    'publico'    => 'Profissionais de RH, coaches, terapeutas, consultores, líderes e profissionais de desenvolvimento humano',
    'topicos'    => '["O que são valores pessoais","Como os valores influenciam comportamento e decisões","Interpretação prática do CVAT","Aplicação em carreira e relacionamentos","Desenvolvimento pessoal e profissional"]',
    'cta_texto'  => 'Solicitar treinamento',
    'cta_link'   => '/pages/contato.html',
    'pagina_url' => null,
    'ativo'      => 1,
    'ordem'      => 1,
  ],
  [
    'id'         => 2,
    'titulo'     => 'Especialista em Carreira CVAT',
    'descricao'  => 'Capacitação voltada ao entendimento de perfil profissional, escolhas de carreira, potencial humano e direcionamento profissional utilizando os indicadores comportamentais do CVAT.',
    'modalidade' => 'EAD (assíncrono)',
    'cor'        => 'blue',
    'categorias' => '["CVAT"]',
    'publico'    => 'Consultores de carreira, RH, mentores, coaches e profissionais de desenvolvimento humano',
    'topicos'    => '["Mapeamento de perfil profissional","Escolha e transição de carreira","Valores e motivadores profissionais","Orientação profissional estratégica","Plano de desenvolvimento de carreira"]',
    'cta_texto'  => 'Solicitar treinamento',
    'cta_link'   => '/pages/contato.html',
    'pagina_url' => null,
    'ativo'      => 1,
    'ordem'      => 2,
  ],
  [
    'id'         => 3,
    'titulo'     => 'Especialista em Relacionamentos CVAT',
    'descricao'  => 'Formação aplicada ao entendimento de perfis comportamentais e valores pessoais nos relacionamentos interpessoais, familiares, profissionais e afetivos.',
    'modalidade' => 'EAD (assíncrono)',
    'cor'        => 'green',
    'categorias' => '["CVAT"]',
    'publico'    => 'Coaches, terapeutas, psicólogos, RH e profissionais de desenvolvimento humano',
    'topicos'    => '["Dinâmica dos relacionamentos","Conflitos comportamentais","Compatibilidade de valores","Comunicação interpessoal","Aplicações práticas do CVAT em relacionamentos"]',
    'cta_texto'  => 'Solicitar treinamento',
    'cta_link'   => '/pages/contato.html',
    'pagina_url' => null,
    'ativo'      => 1,
    'ordem'      => 3,
  ],
  [
    'id'         => 4,
    'titulo'     => 'CVAT Aplicado ao Desenvolvimento Pessoal e Profissional',
    'descricao'  => 'Curso voltado à aplicação prática do CVAT no autoconhecimento, desenvolvimento de competências, liderança, produtividade e crescimento pessoal e profissional.',
    'modalidade' => 'EAD (assíncrono)',
    'cor'        => 'coral',
    'categorias' => '["CVAT"]',
    'publico'    => 'Profissionais em desenvolvimento, estudantes, líderes e consultores',
    'topicos'    => '["Autoconhecimento aplicado","Competências comportamentais","Desenvolvimento profissional","Produtividade e comportamento","Aplicações práticas do CVAT"]',
    'cta_texto'  => 'Solicitar treinamento',
    'cta_link'   => '/pages/contato.html',
    'pagina_url' => null,
    'ativo'      => 1,
    'ordem'      => 4,
  ],
  [
    'id'         => 5,
    'titulo'     => 'Gestor NR-01',
    'descricao'  => 'Formação prática para gestores e profissionais que desejam compreender e implementar a NR-01 com foco em riscos psicossociais, PGR e gestão organizacional preventiva.',
    'modalidade' => 'EAD (assíncrono)',
    'cor'        => 'green',
    'categorias' => '["NR-01"]',
    'publico'    => 'Empresários, gestores, RH, SESMT e consultores',
    'topicos'    => '["Atualizações da NR-01","Riscos psicossociais","PGR aplicado","Gestão preventiva","Documentação e conformidade legal"]',
    'cta_texto'  => 'Solicitar treinamento',
    'cta_link'   => '/pages/contato.html',
    'pagina_url' => null,
    'ativo'      => 1,
    'ordem'      => 5,
  ],
  [
    'id'         => 6,
    'titulo'     => 'CVAT Aplicado à NR-01',
    'descricao'  => 'Capacitação voltada à utilização do CVAT como ferramenta de apoio na identificação de riscos psicossociais, clima organizacional e fatores humanos relacionados à NR-01.',
    'modalidade' => 'EAD (assíncrono)',
    'cor'        => 'coral',
    'categorias' => '["NR-01","CVAT"]',
    'publico'    => 'RH, consultores, SESMT e gestores organizacionais',
    'topicos'    => '["CVAT e riscos psicossociais","Mapeamento comportamental","Clima organizacional","Fatores humanos na NR-01","Indicadores comportamentais preventivos"]',
    'cta_texto'  => 'Solicitar treinamento',
    'cta_link'   => '/pages/contato.html',
    'pagina_url' => null,
    'ativo'      => 1,
    'ordem'      => 6,
  ],
  [
    'id'         => 7,
    'titulo'     => 'NR-01 Lucrativa',
    'descricao'  => 'Curso estratégico voltado para profissionais que desejam transformar a demanda da NR-01 em oportunidade comercial através de consultorias, treinamentos e soluções organizacionais.',
    'modalidade' => 'EAD (assíncrono)',
    'cor'        => 'coral',
    'categorias' => '["NR-01"]',
    'publico'    => 'Consultores, profissionais de SST, RH e empreendedores',
    'topicos'    => '["Mercado da NR-01","Como vender consultoria","Estruturação de serviços","Posicionamento comercial","Oportunidades em riscos psicossociais"]',
    'cta_texto'  => 'Solicitar treinamento',
    'cta_link'   => '/pages/contato.html',
    'pagina_url' => null,
    'ativo'      => 1,
    'ordem'      => 7,
  ],
  [
    'id'         => 8,
    'titulo'     => 'HSE-IT – Avaliação Psicossocial do Trabalho',
    'descricao'  => 'Formação sobre avaliação psicossocial no trabalho com foco em riscos psicossociais, saúde mental ocupacional e utilização do protocolo HSE-IT em organizações.',
    'modalidade' => 'EAD (assíncrono)',
    'cor'        => 'green',
    'categorias' => '["NR-01"]',
    'publico'    => 'Psicólogos, RH, SESMT, consultores e gestores',
    'topicos'    => '["Avaliação psicossocial","Riscos psicossociais","Saúde mental no trabalho","Aplicação do HSE-IT","Prevenção organizacional"]',
    'cta_texto'  => 'Solicitar treinamento',
    'cta_link'   => '/pages/contato.html',
    'pagina_url' => null,
    'ativo'      => 1,
    'ordem'      => 8,
  ],
  [
    'id'         => 9,
    'titulo'     => 'COPSOQ – Formação Avançada em Riscos Psicossociais no Trabalho',
    'descricao'  => 'Capacitação avançada sobre riscos psicossociais utilizando o protocolo COPSOQ, com foco em diagnóstico organizacional, prevenção e gestão da saúde mental no trabalho.',
    'modalidade' => 'EAD (assíncrono)',
    'cor'        => 'coral',
    'categorias' => '["NR-01"]',
    'publico'    => 'RH, SESMT, psicólogos, consultores e pesquisadores',
    'topicos'    => '["Modelo COPSOQ","Diagnóstico psicossocial","Saúde mental organizacional","Fatores de risco no trabalho","Estratégias de prevenção"]',
    'cta_texto'  => 'Solicitar treinamento',
    'cta_link'   => '/pages/contato.html',
    'pagina_url' => '/pages/treinamentos/copsoq.html',
    'ativo'      => 1,
    'ordem'      => 9,
  ],
];

// ── Executa UPDATE (ou INSERT se não existir) ─────────────────
echo '<h3>Atualizando registros...</h3>';
echo '<table><tr><th>ID</th><th>Título</th><th>Resultado</th></tr>';

$sqlUpdate = "INSERT INTO treinamentos
    (id, titulo, descricao, modalidade, cor, categorias, publico, topicos, cta_texto, cta_link, pagina_url, ativo, ordem)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)
    ON DUPLICATE KEY UPDATE
        titulo=VALUES(titulo), descricao=VALUES(descricao), modalidade=VALUES(modalidade),
        cor=VALUES(cor), categorias=VALUES(categorias), publico=VALUES(publico),
        topicos=VALUES(topicos), cta_texto=VALUES(cta_texto), cta_link=VALUES(cta_link),
        pagina_url=VALUES(pagina_url), ativo=VALUES(ativo), ordem=VALUES(ordem)";

$stmt = $pdo->prepare($sqlUpdate);

foreach ($cursos as $c) {
    try {
        $stmt->execute([
            $c['id'], $c['titulo'], $c['descricao'], $c['modalidade'],
            $c['cor'], $c['categorias'], $c['publico'], $c['topicos'],
            $c['cta_texto'], $c['cta_link'], $c['pagina_url'],
            $c['ativo'], $c['ordem'],
        ]);
        $icon = $c['pagina_url'] ? ' <small style="color:#1d4ed8">[tem página de vendas]</small>' : '';
        echo "<tr><td>{$c['id']}</td><td>{$c['titulo']}{$icon}</td><td class='ok'>✅ OK</td></tr>\n";
    } catch (PDOException $e) {
        echo "<tr><td>{$c['id']}</td><td>{$c['titulo']}</td><td class='err'>❌ " . htmlspecialchars($e->getMessage()) . "</td></tr>\n";
    }
}

echo '</table>';

// ── Resumo final ─────────────────────────────────────────────
$total = $pdo->query("SELECT COUNT(*) FROM treinamentos WHERE ativo=1")->fetchColumn();
echo "<p class='ok' style='font-size:1.1rem;margin-top:20px'>✅ Migração concluída! {$total} treinamentos ativos no banco.</p>";
echo "<p style='color:#6b7280;font-size:.85rem;margin-top:12px'>⚠️ Apague este arquivo do servidor após verificar os resultados.</p>";
echo '</body></html>';
