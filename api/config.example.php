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
 * E-mail de destino das notificações dos formulários.
 * Deixe MAIL_TO vazio para desativar o envio (apenas salva no banco).
 */
define('MAIL_TO',        'cvat@cvatbrasil.com.br');
define('MAIL_FROM_NAME', 'CVAT Brasil');

/**
 * SMTP autenticado (Hostinger).
 * Use o e-mail e senha criados em: Painel → E-mails → Contas de e-mail
 */
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 465);
define('SMTP_USER', 'cvat@cvatbrasil.com.br');  // remetente autenticado
define('SMTP_PASS', 'sua-senha-de-email');        // senha da conta de e-mail
