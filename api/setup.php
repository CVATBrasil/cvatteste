<?php
/**
 * Setup automático — cria tabelas e insere dados iniciais
 * Acesse: https://seusite.com/api/setup.php?key=cvat2026
 * APAGUE este arquivo após rodar com sucesso.
 */
if (($_GET['key'] ?? '') !== 'cvat2026') {
    http_response_code(403); exit('Acesso negado');
}

header('Content-Type: text/html; charset=UTF-8');

$configPath = __DIR__ . '/config.php';
if (!file_exists($configPath)) {
    die('<p style="color:red">❌ api/config.php não encontrado. Crie-o primeiro.</p>');
}
require_once $configPath;

function conectar(): PDO {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    return new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

function ok(string $msg): void  { echo "<p>✅ $msg</p>\n"; }
function err(string $msg): void { echo "<p>❌ $msg</p>\n"; }
function info(string $msg): void{ echo "<p>ℹ️  $msg</p>\n"; }

echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8">
<title>Setup CVAT Brasil</title>
<style>body{font-family:sans-serif;max-width:700px;margin:40px auto;padding:0 20px}
h2{color:#1e40af}p{margin:4px 0}hr{margin:20px 0}</style></head><body>';
echo '<h2>Setup CVAT Brasil</h2>';

try {
    $pdo = conectar();
    ok('Conexão com o banco de dados OK');
} catch (Exception $e) {
    err('Falha na conexão: ' . $e->getMessage());
    echo '</body></html>'; exit;
}

// ── SCHEMA ────────────────────────────────────────────────────────
echo '<hr><h3>Criando tabelas...</h3>';

$tabelas = [

'treinamentos' => "CREATE TABLE IF NOT EXISTS `treinamentos` (
  `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `titulo`        VARCHAR(255)    NOT NULL,
  `descricao`     TEXT            NOT NULL,
  `modalidade`    VARCHAR(100)    NOT NULL,
  `cor`           ENUM('blue','green','coral') NOT NULL DEFAULT 'blue',
  `categorias`    JSON            NOT NULL,
  `publico`       VARCHAR(255)    DEFAULT NULL,
  `topicos`       JSON            NOT NULL,
  `cta_texto`     VARCHAR(100)    NOT NULL DEFAULT 'Solicitar treinamento',
  `cta_link`      VARCHAR(500)    NOT NULL DEFAULT '/pages/contato.html',
  `ativo`         TINYINT(1)      NOT NULL DEFAULT 1,
  `ordem`         SMALLINT        NOT NULL DEFAULT 0,
  `criado_em`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ativo_ordem` (`ativo`, `ordem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'blog_posts' => "CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id`               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `destaque`         TINYINT(1)      NOT NULL DEFAULT 0,
  `titulo`           VARCHAR(500)    NOT NULL,
  `excerpt`          TEXT            NOT NULL,
  `categoria`        ENUM('nr01','saude-mental','clima','lideranca','rh') NOT NULL,
  `categoria_label`  VARCHAR(100)    NOT NULL,
  `cor`              ENUM('blue','green','coral') NOT NULL DEFAULT 'blue',
  `data_publicacao`  DATE            NOT NULL,
  `tempo_leitura`    TINYINT UNSIGNED NOT NULL DEFAULT 5,
  `autor_nome`       VARCHAR(255)    NOT NULL,
  `autor_iniciais`   VARCHAR(5)      NOT NULL,
  `autor_cor`        VARCHAR(50)     NOT NULL DEFAULT 'default',
  `link`             VARCHAR(500)    NOT NULL DEFAULT '#',
  `publicado`        TINYINT(1)      NOT NULL DEFAULT 1,
  `criado_em`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_publicado_data` (`publicado`, `data_publicacao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'contatos' => "CREATE TABLE IF NOT EXISTS `contatos` (
  `id`        INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `nome`      VARCHAR(255)  NOT NULL,
  `email`     VARCHAR(255)  NOT NULL,
  `empresa`   VARCHAR(255)  DEFAULT NULL,
  `cargo`     VARCHAR(255)  DEFAULT NULL,
  `telefone`  VARCHAR(50)   DEFAULT NULL,
  `assunto`   VARCHAR(255)  DEFAULT NULL,
  `mensagem`  TEXT          DEFAULT NULL,
  `ip`        VARCHAR(45)   DEFAULT NULL,
  `lido`      TINYINT(1)    NOT NULL DEFAULT 0,
  `criado_em` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'diagnosticos' => "CREATE TABLE IF NOT EXISTS `diagnosticos` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `nome`          VARCHAR(255)  NOT NULL,
  `email`         VARCHAR(255)  NOT NULL,
  `telefone`      VARCHAR(50)   NOT NULL,
  `cargo`         VARCHAR(100)  NOT NULL,
  `empresa`       VARCHAR(255)  NOT NULL,
  `setor`         VARCHAR(100)  NOT NULL,
  `colaboradores` VARCHAR(20)   NOT NULL,
  `interesse`     ENUM('clima','nr01','saude-mental','tudo') NOT NULL,
  `desafio`       TEXT          DEFAULT NULL,
  `ip`            VARCHAR(45)   DEFAULT NULL,
  `lido`          TINYINT(1)    NOT NULL DEFAULT 0,
  `criado_em`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'leads' => "CREATE TABLE IF NOT EXISTS `leads` (
  `id`        INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `email`     VARCHAR(255)  NOT NULL,
  `fonte`     VARCHAR(100)  NOT NULL DEFAULT 'newsletter',
  `ativo`     TINYINT(1)    NOT NULL DEFAULT 1,
  `criado_em` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

];

foreach ($tabelas as $nome => $sql) {
    try {
        $pdo->exec($sql);
        ok("Tabela <strong>$nome</strong> criada / já existia");
    } catch (Exception $e) {
        err("Tabela <strong>$nome</strong>: " . $e->getMessage());
    }
}

// ── SEED — TREINAMENTOS ───────────────────────────────────────────
echo '<hr><h3>Inserindo treinamentos...</h3>';

$count = (int) $pdo->query('SELECT COUNT(*) FROM treinamentos')->fetchColumn();
if ($count > 0) {
    info("Treinamentos já existem ($count registros). Pulando inserção.");
} else {
    $treinamentos = [
        ['C-VAT Aplicado ao Desenvolvimento Pessoal e Profissional',
         'Aprenda a interpretar e aplicar a Ferramenta de Análise de Valores Pessoais e Culturais em processos de coaching, mentoria, RH e consultoria.',
         'Online ao vivo', 'blue', '["consultores","rh","coaches"]',
         'Consultores, Coaches, Profissionais de RH e Gestores',
         '["O que é o C-VAT e sua base científica","Como interpretar o Perfil de Valores Pessoais (PVP)","Como interpretar o Perfil de Valores Agregados (PVA)","Aplicações práticas em seleção, desenvolvimento e liderança","Devolutiva e processos de feedback com o C-VAT"]',
         'Quero me inscrever', '/pages/contato.html', 1],

        ['Formação de Consultores C-VAT',
         'Certificação completa para aplicar o C-VAT profissionalmente em empresas e consultorias. Metodologia usada em mais de 7 países.',
         'Online ao vivo', 'coral', '["consultores"]',
         'Consultores Organizacionais e Independentes',
         '["Fundamentos do C-VAT e comportamento organizacional","Aplicação do instrumento PVP e PVA","Análise e interpretação de perfis","Devolutiva individual e para grupos","Uso do sistema C-VAT e geração de relatórios","Precificação e posicionamento da consultoria"]',
         'Quero me certificar', '/pages/contato.html', 2],

        ['C-VAT para Recrutamento e Seleção',
         'Como usar o perfil C-VAT para contratar com mais assertividade, reduzir turnover e alinhar candidatos à cultura da empresa.',
         'Online ao vivo', 'green', '["rh"]',
         'Profissionais de RH e Recrutadores',
         '["Cultura organizacional e fit cultural","Como aplicar o C-VAT no processo seletivo","Leitura do perfil do candidato x perfil da função","Redução de erros de contratação","Cases práticos e simulações"]',
         'Solicitar treinamento', '/pages/contato.html', 3],

        ['C-VAT para Líderes e Gestores',
         'Entenda o perfil comportamental da sua equipe e tome decisões de gestão com clareza: delegação, desenvolvimento e conversas difíceis.',
         'Presencial ou Online', 'blue', '["lideranca","gestores"]',
         'Líderes, Gestores e Diretores',
         '["Valores pessoais e seu impacto na liderança","Leitura do perfil da equipe com o C-VAT","Delegação baseada em perfil comportamental","Gestão de conflitos com base em valores","Desenvolvimento individual de colaboradores"]',
         'Solicitar treinamento', '/pages/contato.html', 4],

        ['C-VAT para Coaches e Mentores',
         'Potencialize seus processos de coaching e mentoria com dados objetivos sobre valores, comportamentos e padrões do seu cliente.',
         'Online ao vivo', 'green', '["coaches"]',
         'Coaches, Mentores e Orientadores Vocacionais',
         '["Como o C-VAT complementa metodologias de coaching","Aplicação e devolutiva em processos de desenvolvimento","Leitura de padrões positivos e negativos","Uso do C-VAT em orientação vocacional e de carreira","Ética e confidencialidade na aplicação"]',
         'Quero aprender', '/pages/contato.html', 5],
    ];

    $sql = 'INSERT INTO treinamentos (titulo, descricao, modalidade, cor, categorias, publico, topicos, cta_texto, cta_link, ordem) VALUES (?,?,?,?,?,?,?,?,?,?)';
    $stmt = $pdo->prepare($sql);
    foreach ($treinamentos as $t) {
        try {
            $stmt->execute($t);
            ok("Treinamento inserido: <strong>{$t[0]}</strong>");
        } catch (Exception $e) {
            err("Erro em <strong>{$t[0]}</strong>: " . $e->getMessage());
        }
    }
}

// ── SEED — BLOG ───────────────────────────────────────────────────
echo '<hr><h3>Inserindo artigos do blog...</h3>';

$count = (int) $pdo->query('SELECT COUNT(*) FROM blog_posts')->fetchColumn();
if ($count > 0) {
    info("Blog posts já existem ($count registros). Pulando inserção.");
} else {
    $posts = [
        [1, 'O que é o C-VAT e como ele pode transformar decisões sobre pessoas',
         'A Ferramenta de Análise de Valores Pessoais e Culturais foi criada na Cornell University nos anos 1980 e hoje é usada em 7 países. Entenda como ela funciona.',
         'clima', 'Cultura Organizacional', 'blue', '2025-04-01', 6,
         'Clóvis Soler', 'CS', 'blue', '#', 1],
        [0, 'Como o perfil comportamental ajuda na contratação certa',
         'Usar o C-VAT no processo seletivo reduz erros de contratação e aumenta o alinhamento cultural. Veja como aplicar na prática.',
         'rh', 'Recursos Humanos', 'green', '2025-04-15', 5,
         'Max Gines', 'MG', 'green', '#', 1],
        [0, 'Valores pessoais x Valores organizacionais: quando há conflito',
         'O desalinhamento entre os valores do colaborador e da empresa é uma das principais causas de turnover e baixo engajamento. Saiba identificar e agir.',
         'clima', 'Clima Organizacional', 'coral', '2025-05-01', 7,
         'Clóvis Soler', 'CS', 'blue', '#', 1],
        [0, 'C-VAT na prática: case de uso em processo de coaching executivo',
         'Como o perfil de valores pessoais foi usado para ajudar um executivo a tomar decisões mais alinhadas com sua essência e objetivos de carreira.',
         'lideranca', 'Liderança', 'blue', '2025-05-10', 5,
         'Max Gines', 'MG', 'green', '#', 1],
        [0, 'Por que o C-VAT foi eleito Melhor Ferramenta de Assessment da Ásia em 2015',
         'Em Singapura, milhares de profissionais de RH elegeram o C-VAT como a ferramenta de assessment mais confiável e precisa. Entenda os motivos.',
         'rh', 'Recursos Humanos', 'green', '2025-05-20', 4,
         'Clóvis Soler', 'CS', 'blue', '#', 1],
    ];

    $sql = 'INSERT INTO blog_posts (destaque, titulo, excerpt, categoria, categoria_label, cor, data_publicacao, tempo_leitura, autor_nome, autor_iniciais, autor_cor, link, publicado) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)';
    $stmt = $pdo->prepare($sql);
    foreach ($posts as $p) {
        try {
            $stmt->execute($p);
            ok("Post inserido: <strong>{$p[1]}</strong>");
        } catch (Exception $e) {
            err("Erro em <strong>{$p[1]}</strong>: " . $e->getMessage());
        }
    }
}

// ── RESUMO ────────────────────────────────────────────────────────
echo '<hr><h3>Resumo final</h3>';
foreach (['treinamentos','blog_posts','contatos','diagnosticos','leads'] as $t) {
    $n = $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
    info("Tabela <strong>$t</strong>: $n registro(s)");
}

echo '<hr><p style="color:#dc2626"><strong>⚠️ APAGUE este arquivo do servidor após confirmar que tudo está funcionando!</strong></p>';
echo '</body></html>';
