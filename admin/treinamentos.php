<?php
/**
 * Admin de Treinamentos — CVAT Brasil
 * Acesse: /admin/treinamentos.php?key=cvat2026
 */
declare(strict_types=1);
session_start();

define('ADMIN_KEY',  'cvat2026');
define('ADMIN_PASS', 'cvat2026');   // ← altere para uma senha segura

// ── Autenticação ──────────────────────────────────────────────
if (isset($_GET['key']) && $_GET['key'] === ADMIN_KEY) {
    $_SESSION['cvat_admin'] = true;
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?')); exit;
}

if (!($_SESSION['cvat_admin'] ?? false)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['senha'] ?? '') === ADMIN_PASS) {
        $_SESSION['cvat_admin'] = true;
        header('Location: ' . $_SERVER['PHP_SELF']); exit;
    }
    loginForm(); exit;
}

if (isset($_GET['sair'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']); exit;
}

// ── Banco de dados ─────────────────────────────────────────────
$configPath = dirname(__DIR__) . '/api/config.php';
if (!file_exists($configPath)) { die('api/config.php não encontrado.'); }
require_once $configPath;

function db(): PDO {
    static $pdo;
    if (!$pdo) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    }
    return $pdo;
}

// Auto-migração: adiciona pagina_url se ainda não existir
try { db()->exec("ALTER TABLE treinamentos ADD COLUMN `pagina_url` VARCHAR(500) DEFAULT NULL AFTER `cta_link`"); }
catch (PDOException) { /* coluna já existe */ }

// ── Ações POST ────────────────────────────────────────────────
$flash = $_GET['msg'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle') {
        db()->prepare("UPDATE treinamentos SET ativo = IF(ativo=1,0,1) WHERE id=?")->execute([(int)$_POST['id']]);
        redirect('Status atualizado.');
    }

    if ($action === 'delete') {
        db()->prepare("DELETE FROM treinamentos WHERE id=?")->execute([(int)$_POST['id']]);
        redirect('Treinamento excluído.');
    }

    if ($action === 'save') {
        $categorias = linesToJson($_POST['categorias'] ?? '');
        $topicos    = linesToJson($_POST['topicos']    ?? '');
        $id         = (int)($_POST['id'] ?? 0);
        $pagina_url = trim($_POST['pagina_url'] ?? '') ?: null;

        $fields = [
            $_POST['titulo']     ?? '',
            $_POST['descricao']  ?? '',
            $_POST['modalidade'] ?? '',
            $_POST['cor']        ?? 'blue',
            $categorias,
            $_POST['publico']    ?? '',
            $topicos,
            $_POST['cta_texto']  ?? 'Solicitar treinamento',
            $_POST['cta_link']   ?? '/pages/contato.html',
            $pagina_url,
            isset($_POST['ativo']) ? 1 : 0,
            (int)($_POST['ordem'] ?? 0),
        ];

        if ($id === 0) {
            db()->prepare("INSERT INTO treinamentos
                (titulo,descricao,modalidade,cor,categorias,publico,topicos,cta_texto,cta_link,pagina_url,ativo,ordem)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?)")->execute($fields);
            redirect('Treinamento adicionado com sucesso!');
        } else {
            $fields[] = $id;
            db()->prepare("UPDATE treinamentos
                SET titulo=?,descricao=?,modalidade=?,cor=?,categorias=?,publico=?,topicos=?,
                    cta_texto=?,cta_link=?,pagina_url=?,ativo=?,ordem=?
                WHERE id=?")->execute($fields);
            redirect('Treinamento salvo com sucesso!');
        }
    }
}

// ── Dados ─────────────────────────────────────────────────────
$trainings = db()->query("SELECT * FROM treinamentos ORDER BY ordem, id")->fetchAll();
$editId    = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
$editItem  = null;
$newItem   = isset($_GET['novo']);

if ($editId) {
    foreach ($trainings as $t) {
        if ((int)$t['id'] === $editId) { $editItem = $t; break; }
    }
}

// ── Helpers ───────────────────────────────────────────────────
function linesToJson(string $text): string {
    $lines = array_values(array_filter(array_map('trim', explode("\n", $text))));
    return json_encode($lines, JSON_UNESCAPED_UNICODE);
}

function jsonToLines(?string $json): string {
    if (!$json) return '';
    $arr = json_decode($json, true);
    return is_array($arr) ? implode("\n", $arr) : '';
}

function redirect(string $msg): void {
    header('Location: ' . strtok($_SERVER['PHP_SELF'], '?') . '?msg=' . urlencode($msg)); exit;
}

function loginForm(): void { ?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin — CVAT Brasil</title>
<style>
  body{font-family:sans-serif;background:#f1f5f9;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
  .box{background:#fff;border-radius:16px;padding:40px;width:320px;box-shadow:0 8px 32px rgba(0,0,0,.1)}
  h2{color:#1e40af;margin:0 0 24px;font-size:1.4rem}
  input{width:100%;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:1rem;box-sizing:border-box;margin-bottom:16px}
  button{width:100%;padding:12px;background:#1A56DB;color:#fff;border:none;border-radius:8px;font-size:1rem;font-weight:700;cursor:pointer}
  button:hover{background:#1040B0}
</style></head><body>
<div class="box">
  <h2>Admin CVAT Brasil</h2>
  <form method="post">
    <input type="password" name="senha" placeholder="Senha de acesso" autofocus required>
    <button type="submit">Entrar</button>
  </form>
</div></body></html>
<?php }

function corBadge(string $cor): string {
    return match($cor) {
        'green' => 'background:#d1fae5;color:#065f46',
        'coral' => 'background:#fde8de;color:#9a2a0a',
        default => 'background:#ebf1ff;color:#1040b0',
    };
}

function field(string $label, string $name, string $value = '', string $type = 'text', bool $required = false): string {
    $req = $required ? ' required' : '';
    $v   = htmlspecialchars($value);
    return "<label><span>{$label}</span><input type=\"{$type}\" name=\"{$name}\" value=\"{$v}\"{$req}></label>\n";
}

function textarea(string $label, string $name, string $value = '', int $rows = 3, string $help = ''): string {
    $v = htmlspecialchars($value);
    $h = $help ? "<small>{$help}</small>" : '';
    return "<label><span>{$label}</span>{$h}<textarea name=\"{$name}\" rows=\"{$rows}\">{$v}</textarea></label>\n";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Treinamentos — CVAT Brasil</title>
<style>
/* ── Reset & base ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f1f5f9; color: #1e293b; font-size: 14px; }

/* ── Layout ── */
.topbar { background: #1e40af; color: #fff; padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
.topbar h1 { font-size: 1rem; font-weight: 700; }
.topbar a { color: rgba(255,255,255,.75); font-size: .85rem; text-decoration: none; }
.topbar a:hover { color: #fff; }
.container { max-width: 1100px; margin: 0 auto; padding: 24px 16px; }

/* ── Flash ── */
.flash { background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; font-weight: 600; }

/* ── Card ── */
.card { background: #fff; border: 1.5px solid #e5e7eb; border-radius: 16px; overflow: hidden; margin-bottom: 24px; }
.card-header { padding: 16px 20px; background: #f8fafc; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.card-header h2 { font-size: .95rem; font-weight: 700; color: #1e293b; }
.card-body { padding: 20px; }

/* ── Tabela ── */
table { width: 100%; border-collapse: collapse; font-size: .875rem; }
th { text-align: left; padding: 10px 12px; background: #f8fafc; border-bottom: 1.5px solid #e5e7eb; font-weight: 700; color: #64748b; font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap; }
td { padding: 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
tr:last-child td { border-bottom: none; }
tr.inativo td { opacity: .45; }
.ordem { width: 40px; text-align: center; color: #94a3b8; font-weight: 700; }

/* ── Badges ── */
.badge { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: .75rem; font-weight: 700; }
.pill-on  { background: #d1fae5; color: #065f46; }
.pill-off { background: #fee2e2; color: #991b1b; }
.pill-page { background: #eff6ff; color: #1d4ed8; font-size: .7rem; }

/* ── Botões ── */
.btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 8px; font-size: .82rem; font-weight: 700; cursor: pointer; border: none; text-decoration: none; transition: .15s; white-space: nowrap; }
.btn-primary { background: #1A56DB; color: #fff; }
.btn-primary:hover { background: #1040B0; }
.btn-outline { background: transparent; color: #475569; border: 1.5px solid #e5e7eb; }
.btn-outline:hover { border-color: #1A56DB; color: #1A56DB; }
.btn-danger { background: transparent; color: #dc2626; border: 1.5px solid #fecaca; }
.btn-danger:hover { background: #fef2f2; }
.btn-green { background: #059669; color: #fff; }
.btn-green:hover { background: #047857; }
.btn-sm { padding: 5px 10px; font-size: .78rem; }
.btn-actions { display: flex; gap: 6px; flex-wrap: wrap; }

/* ── Formulário ── */
.form-grid { display: grid; gap: 16px; }
@media (min-width: 640px) { .form-grid-2 { grid-template-columns: 1fr 1fr; } }
@media (min-width: 900px) { .form-grid-3 { grid-template-columns: 1fr 1fr 1fr; } }

label { display: flex; flex-direction: column; gap: 5px; }
label span { font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #64748b; }
label small { color: #94a3b8; font-size: .75rem; margin-top: -2px; }
input[type="text"], input[type="number"], input[type="url"], select, textarea {
    width: 100%; padding: 9px 12px; border: 1.5px solid #e5e7eb; border-radius: 8px;
    font-size: .9rem; font-family: inherit; transition: border-color .15s;
}
input:focus, select:focus, textarea:focus { outline: none; border-color: #1A56DB; box-shadow: 0 0 0 3px rgba(26,86,219,.12); }
textarea { resize: vertical; }
.check-row { flex-direction: row; align-items: center; gap: 10px; }
.check-row input[type="checkbox"] { width: 18px; height: 18px; }

.form-section { padding: 20px; border-top: 1px solid #f1f5f9; }
.form-section:first-child { border-top: none; }
.form-section-title { font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
.form-section-title::after { content: ''; flex: 1; height: 1px; background: #f1f5f9; }

.form-actions { padding: 16px 20px; background: #f8fafc; border-top: 1px solid #e5e7eb; display: flex; gap: 10px; align-items: center; }

.highlight { background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 10px; padding: 12px 16px; font-size: .85rem; color: #92400e; margin-bottom: 20px; }
.highlight strong { display: block; margin-bottom: 4px; }
</style>
</head>
<body>

<div class="topbar">
  <h1>⚙️ Admin — Treinamentos CVAT Brasil</h1>
  <div style="display:flex;gap:16px;align-items:center">
    <a href="/pages/treinamentos.html" target="_blank">↗ Ver página</a>
    <a href="?sair">Sair</a>
  </div>
</div>

<div class="container">

  <?php if ($flash): ?>
    <div class="flash">✅ <?= htmlspecialchars($flash) ?></div>
  <?php endif; ?>

  <?php if ($editItem || $newItem): ?>
    <?php $item = $editItem ?? []; $isNew = $newItem; ?>

    <!-- ── Formulário de edição / novo ── -->
    <div class="card">
      <div class="card-header">
        <h2><?= $isNew ? '➕ Novo treinamento' : '✏️ Editando: ' . htmlspecialchars($item['titulo'] ?? '') ?></h2>
        <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-outline btn-sm">← Voltar</a>
      </div>

      <form method="post">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int)($item['id'] ?? 0) ?>">

        <!-- Informações principais -->
        <div class="form-section">
          <p class="form-section-title">Informações principais</p>
          <div class="form-grid form-grid-2">
            <?= field('Título *', 'titulo', $item['titulo'] ?? '', 'text', true) ?>
            <?= field('Modalidade *', 'modalidade', $item['modalidade'] ?? 'Online ao vivo', 'text', true) ?>
          </div>
          <div class="form-grid" style="margin-top:16px">
            <?= textarea('Descrição curta *', 'descricao', $item['descricao'] ?? '', 3) ?>
          </div>
          <div class="form-grid form-grid-3" style="margin-top:16px">
            <label>
              <span>Cor do card *</span>
              <select name="cor">
                <?php foreach (['blue' => 'Azul', 'green' => 'Verde', 'coral' => 'Coral'] as $v => $l): ?>
                  <option value="<?= $v ?>" <?= ($item['cor'] ?? 'blue') === $v ? 'selected' : '' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <?= field('Público-alvo', 'publico', $item['publico'] ?? '') ?>
            <?= field('Ordem de exibição', 'ordem', (string)($item['ordem'] ?? 0), 'number') ?>
          </div>
        </div>

        <!-- Categorias e tópicos -->
        <div class="form-section">
          <p class="form-section-title">Categorias e conteúdo</p>
          <div class="form-grid form-grid-2">
            <?= textarea(
                'Categorias (uma por linha)',
                'categorias',
                jsonToLines($item['categorias'] ?? ''),
                4,
                'Valores válidos: rh, lideranca, consultores, equipes, coaches, gestores'
            ) ?>
            <?= textarea(
                'Tópicos do card (um por linha, máx 4 exibidos)',
                'topicos',
                jsonToLines($item['topicos'] ?? ''),
                5,
                'Cada linha vira um item com ✓ no card'
            ) ?>
          </div>
        </div>

        <!-- CTA e página de vendas -->
        <div class="form-section">
          <p class="form-section-title">Botão e página de vendas</p>
          <div class="highlight">
            <strong>💡 Página de vendas (campo abaixo)</strong>
            Se preenchido, o botão do card mostrará <em>"Ver curso completo"</em> e levará para essa URL.
            Se vazio, o botão mostrará o texto e link padrão definidos ao lado.
          </div>
          <div class="form-grid form-grid-3">
            <?= field('URL da página de vendas', 'pagina_url', $item['pagina_url'] ?? '', 'url') ?>
            <?= field('Texto do botão padrão', 'cta_texto', $item['cta_texto'] ?? 'Solicitar treinamento') ?>
            <?= field('Link do botão padrão', 'cta_link', $item['cta_link'] ?? '/pages/contato.html', 'url') ?>
          </div>
        </div>

        <!-- Status -->
        <div class="form-section">
          <p class="form-section-title">Status</p>
          <label class="check-row">
            <input type="checkbox" name="ativo" value="1" <?= ($item['ativo'] ?? 1) ? 'checked' : '' ?>>
            <span style="font-size:.9rem;font-weight:600;text-transform:none;letter-spacing:0;color:#1e293b">
              Ativo — aparece na página de treinamentos
            </span>
          </label>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">
            <?= $isNew ? '➕ Adicionar treinamento' : '💾 Salvar alterações' ?>
          </button>
          <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-outline">Cancelar</a>
        </div>

      </form>
    </div>

  <?php else: ?>

    <!-- ── Lista de treinamentos ── -->
    <div class="card">
      <div class="card-header">
        <h2>Treinamentos (<?= count($trainings) ?> registros)</h2>
        <a href="?novo" class="btn btn-primary btn-sm">➕ Novo treinamento</a>
      </div>
      <div style="overflow-x:auto">
        <table>
          <thead>
            <tr>
              <th class="ordem">#</th>
              <th>Título</th>
              <th>Modalidade</th>
              <th>Cor</th>
              <th>Página de vendas</th>
              <th>Status</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($trainings as $t): ?>
            <tr class="<?= $t['ativo'] ? '' : 'inativo' ?>">
              <td class="ordem"><?= (int)$t['ordem'] ?></td>
              <td>
                <strong><?= htmlspecialchars($t['titulo']) ?></strong>
                <div style="font-size:.78rem;color:#94a3b8;margin-top:3px">
                  <?= htmlspecialchars($t['publico'] ?? '') ?>
                </div>
              </td>
              <td><?= htmlspecialchars($t['modalidade']) ?></td>
              <td>
                <span class="badge" style="<?= corBadge($t['cor']) ?>"><?= $t['cor'] ?></span>
              </td>
              <td>
                <?php if ($t['pagina_url']): ?>
                  <span class="badge pill-page" title="<?= htmlspecialchars($t['pagina_url']) ?>">
                    ✓ Página configurada
                  </span>
                <?php else: ?>
                  <span style="color:#cbd5e1;font-size:.8rem">—</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge <?= $t['ativo'] ? 'pill-on' : 'pill-off' ?>">
                  <?= $t['ativo'] ? 'Ativo' : 'Oculto' ?>
                </span>
              </td>
              <td>
                <div class="btn-actions">
                  <a href="?edit=<?= $t['id'] ?>" class="btn btn-outline btn-sm">✏️ Editar</a>
                  <form method="post" style="display:inline">
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                    <button type="submit" class="btn <?= $t['ativo'] ? 'btn-outline' : 'btn-green' ?> btn-sm">
                      <?= $t['ativo'] ? '⊘ Ocultar' : '✓ Exibir' ?>
                    </button>
                  </form>
                  <form method="post" style="display:inline" onsubmit="return confirm('Excluir permanentemente?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <p style="font-size:.8rem;color:#94a3b8;text-align:center">
      A ordem de exibição é controlada pelo campo "Ordem de exibição" (menor número aparece primeiro).
      Treinamentos ocultos não aparecem no site, mas ficam salvos.
    </p>

  <?php endif; ?>

</div>
</body>
</html>
