-- ============================================================
-- CVAT Brasil — Schema MySQL
-- Compatível com MySQL 8.0+ e MariaDB 10.5+
-- Execute: mysql -u usuario -p nome_do_banco < schema.sql
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- Tabela: treinamentos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `treinamentos` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `titulo`       VARCHAR(255)    NOT NULL,
  `descricao`    TEXT            NOT NULL,
  `modalidade`   VARCHAR(100)    NOT NULL,
  `cor`          ENUM('blue','green','coral') NOT NULL DEFAULT 'blue',
  `categorias`   JSON            NOT NULL COMMENT 'Ex: ["rh","equipes"]',
  `publico`      VARCHAR(255)    DEFAULT NULL,
  `topicos`      JSON            NOT NULL COMMENT 'Ex: ["Tópico 1","Tópico 2"]',
  `cta_texto`    VARCHAR(100)    NOT NULL DEFAULT 'Solicitar treinamento',
  `cta_link`     VARCHAR(500)    NOT NULL DEFAULT '/pages/contato.html',
  `ativo`        TINYINT(1)      NOT NULL DEFAULT 1,
  `ordem`        SMALLINT        NOT NULL DEFAULT 0,
  `criado_em`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ativo_ordem` (`ativo`, `ordem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabela: blog_posts
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_posts` (
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
  KEY `idx_publicado_data` (`publicado`, `data_publicacao`),
  KEY `idx_destaque` (`destaque`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabela: contatos  (submissões do formulário de contato)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contatos` (
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
  PRIMARY KEY (`id`),
  KEY `idx_lido` (`lido`),
  KEY `idx_criado_em` (`criado_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabela: leads  (assinantes da newsletter)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `leads` (
  `id`        INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `email`     VARCHAR(255)  NOT NULL,
  `fonte`     VARCHAR(100)  NOT NULL DEFAULT 'newsletter',
  `ativo`     TINYINT(1)    NOT NULL DEFAULT 1,
  `criado_em` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email` (`email`),
  KEY `idx_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabela: diagnosticos  (submissões do diagnóstico gratuito)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `diagnosticos` (
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
  PRIMARY KEY (`id`),
  KEY `idx_lido` (`lido`),
  KEY `idx_criado_em` (`criado_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
