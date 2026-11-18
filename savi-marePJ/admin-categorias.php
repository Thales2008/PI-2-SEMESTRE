<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();
seed_initial_catalog();

$pdo = db();
$error = null;
$message = take_flash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
  $action = (string)($_POST['action'] ?? 'save');

  if ($action === 'delete') {
    $deleteId = (int)($_POST['id'] ?? 0);
    try {
      $del = $pdo->prepare('DELETE FROM categories WHERE id = :id');
      $del->execute(['id' => $deleteId]);
      flash('success', 'Categoria excluída com sucesso.');
    } catch (PDOException $e) {
      flash('error', 'Não é possível excluir uma categoria que possui produtos vinculados.');
    }
    header('Location: admin-categorias.php');
    exit;
  }

  $id = (int)($_POST['id'] ?? 0);
  $name = trim((string)($_POST['name'] ?? ''));
  $description = trim((string)($_POST['description'] ?? ''));

  if ($name === '') {
    $error = 'Informe o nome da categoria.';
  } else {
    $slug = slugify($name);
    try {
      if ($id > 0) {
        $upd = $pdo->prepare('UPDATE categories SET name = :name, slug = :slug, description = :desc WHERE id = :id');
        $upd->execute(['name' => $name, 'slug' => $slug, 'desc' => $description !== '' ? $description : null, 'id' => $id]);
        flash('success', 'Categoria atualizada com sucesso.');
      } else {
        $ins = $pdo->prepare('INSERT INTO categories (name, slug, description) VALUES (:name, :slug, :desc)');
        $ins->execute(['name' => $name, 'slug' => $slug, 'desc' => $description !== '' ? $description : null]);
        flash('success', 'Categoria cadastrada com sucesso.');
      }
      header('Location: admin-categorias.php');
      exit;
    } catch (PDOException $e) {
      $error = 'Já existe uma categoria com este nome.';
    }
  }
}

$editing = null;
$editId = (int)($_GET['editar'] ?? 0);
if ($editId > 0) {
  $st = $pdo->prepare('SELECT * FROM categories WHERE id = :id LIMIT 1');
  $st->execute(['id' => $editId]);
  $editing = $st->fetch() ?: null;
}

$categories = $pdo->query(
  'SELECT c.*, COUNT(p.id) AS total_products
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id
     ORDER BY c.name ASC'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gerenciar Categorias | Savi Mare Sociale</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <link rel="icon" type="image/png" href="assets/logo2.png">
</head>

<body>

  <?php require __DIR__ . '/includes/header.php'; ?>

  <main class="admin">
    <div class="admin-top">
      <div>
        <div class="section-label">Painel administrativo</div>
        <h1 class="section-title">Categorias</h1>
      </div>
      <div class="hero-actions">
        <a href="admin-produtos.php" class="button button-dark">Gerenciar produtos</a>
      </div>
    </div>

    <?php if ($message): ?><p class="notice <?= e($message['type']) ?>" role="status"><?= e($message['message']) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>

    <div class="admin-grid">
      <div class="panel">
        <h2>Categorias cadastradas (<?= count($categories) ?>)</h2>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Produtos</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($categories as $cat): ?>
                <tr>
                  <td><strong><?= e($cat['name']) ?></strong></td>
                  <td><?= e($cat['description'] ?: '—') ?></td>
                  <td><?= (int)$cat['total_products'] ?></td>
                  <td>
                    <div class="row-actions">
                      <a href="admin-categorias.php?editar=<?= (int)$cat['id'] ?>">Editar</a>
                      <form action="admin-categorias.php" method="post" onsubmit="return confirm('Excluir esta categoria?');" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
                        <button type="submit" class="danger" style="background:none;border:none;border-bottom:1px solid var(--light-gray);cursor:pointer;font:inherit;">Excluir</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="panel">
        <h2><?= $editing ? 'Editar categoria' : 'Nova categoria' ?></h2>
        <form class="auth-form" action="admin-categorias.php" method="post">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="id" value="<?= (int)($editing['id'] ?? 0) ?>">

          <label class="field">
            <span>Nome da categoria *</span>
            <input type="text" name="name" value="<?= e($editing['name'] ?? '') ?>" placeholder="Ex.: Kits Padrinhos" required>
          </label>

          <label class="field">
            <span>Descrição</span>
            <textarea name="description" placeholder="Descrição breve da categoria"><?= e($editing['description'] ?? '') ?></textarea>
          </label>

          <button type="submit" class="button button-dark auth-submit">
            <?= $editing ? 'Salvar alterações' : 'Cadastrar categoria' ?>
          </button>
          <?php if ($editing): ?>
            <a href="admin-categorias.php" class="button button-light" style="width:100%;">Cancelar edição</a>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </main>

  <?php require __DIR__ . '/includes/footer.php'; ?>
</body>

</html>