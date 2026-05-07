<?php
/**
 * Configuração do banco de dados — CVAT Brasil
 *
 * 1. Copie este arquivo: cp config.example.php config.php
 * 2. Preencha com as credenciais reais do seu banco na Hostinger
 * 3. O arquivo config.php está no .gitignore — nunca o comite
 *
 * Na Hostinger, as credenciais ficam em:
 *   Painel → Bancos de Dados MySQL → Detalhes da conexão
 */

define('DB_HOST',    'localhost');        // geralmente localhost na Hostinger
define('DB_NAME',    'seu_banco');        // nome do banco criado no painel
define('DB_USER',    'seu_usuario');      // usuário do banco
define('DB_PASS',    'sua_senha');        // senha do banco
define('DB_CHARSET', 'utf8mb4');

/**
 * E-mail que receberá cópia das mensagens do formulário de contato.
 * Deixe vazio para não enviar e-mail (apenas salvar no banco).
 */
define('MAIL_TO',   'contato@cvatbrasil.com.br');
define('MAIL_FROM', 'noreply@cvatbrasil.com.br');
