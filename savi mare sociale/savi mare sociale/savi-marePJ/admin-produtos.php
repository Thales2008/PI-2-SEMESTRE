<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();
seed_initial_catalog();

$pdo = db();
$error = null;
$message = take_flash();

$categories = $pdo->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();
$colorGroups = ['Neutras', 'Verdes', 'Rosas', 'Azuis', 'Marrons', 'Outras cores'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
  $action = (string)($_POST['action'] ?? 'save');

  if ($action === 'delete') {
    $deleteId = (int)($_POST['id'] ?? 0);
    if ($deleteId > 0) {
      $del = $pdo->prepare('DELETE FROM products WHERE id = :id');
      $del->execute(['id' => $deleteId]);
      flash('success', 'Produto excluído com sucesso.');
    }
    header('Location: admin-produtos.php');
    exit;
  }

  $id = (int)($_POST['id'] ?? 0);
  $name = trim((string)($_POST['name'] ?? ''));
  $categoryId = (int)($_POST['category_id'] ?? 0);
  $colorName = trim((string)($_POST['color_name'] ?? ''));
  $colorGroup = (string)($_POST['color_group'] ?? 'Outras cores');
  $description = trim((string)($_POST['description'] ?? ''));
  $price = (float)str_replace(',', '.', (string)($_POST['price'] ?? '0'));
  $oldPriceRaw = trim((string)($_POST['old_price'] ?? ''));
  $oldPrice = $oldPriceRaw !== '' ? (float)str_replace(',', '.', $oldPriceRaw) : null;
  $rating = min(5.0, max(1.0, (float)str_replace(',', '.', (string)($_POST['rating'] ?? '5.0'))));
  $soldCount = max(0, (int)($_POST['sold_count'] ?? 0));
  $purchaseUrl = trim((string)($_POST['purchase_url'] ?? ''));
  $isBestSeller = isset($_POST['is_best_seller']) ? 1 : 0;
  $bestLabel = trim((string)($_POST['best_seller_label'] ?? ''));
  $active = isset($_POST['active']) ? 1 : 0;
  $imagePath = trim((string)($_POST['image_path'] ?? ''));

  if (!empty($_FILES['image_file']['name']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES['image_file']['tmp_name']);
    if (!isset($allowed[$mime])) {
      $error = 'Formato de imagem inválido. Envie JPG, PNG ou WEBP.';
    } else {
      $destDir = __DIR__ . '/assets/produtos';
      if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
      }
      $fileName = slugify($name ?: 'produto') . '-' . time() . '.' . $allowed[$mime];
      if (move_uploaded_file($_FILES['image_file']['tmp_name'], $destDir . '/' . $fileName)) {
        $imagePath = 'assets/produtos/' . $fileName;
      } else {
        $error = 'Falha ao salvar a imagem enviada.';
      }
    }
  }

  if ($error === null) {
    if ($name === '' || $categoryId <= 0 || $colorName === '' || $price <= 0 || $imagePath === '') {
      $error = 'Preencha nome, categoria, cor, preço válido e imagem do produto.';
    } else {
      if (!in_array($colorGroup, $colorGroups, true)) {
        $colorGroup = 'Outras cores';
      }
      $discount = ($oldPrice !== null && $oldPrice > $price)
        ? (int)round((1 - ($price / $oldPrice)) * 100)
        : 0;

      $slug = slugify($name);
      $checkSlug = $pdo->prepare('SELECT id FROM products WHERE slug = :slug AND id != :id LIMIT 1');
      $checkSlug->execute(['slug' => $slug, 'id' => $id]);
      if ($checkSlug->fetch()) {
        $slug .= '-' . random_int(10, 99);
      }

      if ($id > 0) {
        $upd = $pdo->prepare(
          'UPDATE products SET
                      category_id = :cat, name = :name, slug = :slug, color_name = :color,
                      color_group = :cgroup, description = :desc, price = :price, old_price = :old,
                      discount_percent = :disc, rating = :rating, sold_count = :sold, image = :img,
                      purchase_url = :url, is_best_seller = :best, best_seller_label = :blabel, active = :active
                     WHERE id = :id'
        );
        $upd->execute([
          'cat' => $categoryId,
          'name' => $name,
          'slug' => $slug,
          'color' => $colorName,
          'cgroup' => $colorGroup,
          'desc' => $description,
          'price' => $price,
          'old' => $oldPrice,
          'disc' => $discount,
          'rating' => $rating,
          'sold' => $soldCount,
          'img' => $imagePath,
          'url' => $purchaseUrl !== '' ? $purchaseUrl : null,
          'best' => $isBestSeller,
          'blabel' => $bestLabel !== '' ? $bestLabel : null,
          'active' => $active,
          'id' => $id,
        ]);
        flash('success', 'Produto atualizado com sucesso.');
      } else {
        $ins = $pdo->prepare(
          'INSERT INTO products
                     (category_id, name, slug, color_name, color_group, description, price, old_price, discount_percent, rating, sold_count, image, purchase_url, is_best_seller, best_seller_label, active)
                     VALUES
                     (:cat, :name, :slug, :color, :cgroup, :desc, :price, :old, :disc, :rating, :sold, :img, :url, :best, :blabel, :active)'
        );
        $ins->execute([
          'cat' => $categoryId,
          'name' => $name,
          'slug' => $slug,
          'color' => $colorName,
          'cgroup' => $colorGroup,
          'desc' => $description,
          'price' => $price,
          'old' => $oldPrice,
          'disc' => $discount,
          'rating' => $rating,
          'sold' => $soldCount,
          'img' => $imagePath,
          'url' => $purchaseUrl !== '' ? $purchaseUrl : null,
          'best' => $isBestSeller,
          'blabel' => $bestLabel !== '' ? $bestLabel : null,
          'active' => $active,
        ]);
        flash('success', 'Produto cadastrado com sucesso.');
      }
      header('Location: admin-produtos.php');
      exit;
    }
  }
}

$editing = null;
$editId = (int)($_GET['editar'] ?? 0);
if ($editId > 0) {
  $st = $pdo->prepare('SELECT * FROM products WHERE id = :id LIMIT 1');
  $st->execute(['id' => $editId]);
  $editing = $st->fetch() ?: null;
}

$products = $pdo->query(
  'SELECT p.*, c.name AS category_name
     FROM products p
     INNER JOIN categories c ON c.id = p.category_id
     ORDER BY p.id DESC'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gerenciar Produtos | Savi Mare Sociale</title>
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
        <h1 class="section-title">Produtos</h1>
      </div>
      <div class="hero-actions">
        <a href="admin-categorias.php" class="button button-light">Gerenciar categorias</a>
        <a href="catalogo.php" class="button button-dark">Ver loja</a>
      </div>
    </div>

    <?php if ($message): ?><p class="notice <?= e($message['type']) ?>" role="status"><?= e($message['message']) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>

    <div class="admin-grid">
      <div class="panel">
        <h2>Produtos cadastrados (<?= count($products) ?>)</h2>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Foto</th>
                <th>Produto</th>
                <th>Categoria</th>
                <th>Cor / Tom</th>
                <th>Preço</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($products as $prod): ?>
                <tr>
                  <td><img src="<?= e($prod['image']) ?>" alt="<?= e($prod['name']) ?>"></td>
                  <td>
                    <strong><?= e($prod['name']) ?></strong>
                    <?php if (!(int)$prod['active']): ?>
                      <span style="color:#a4452c;font-size:11px;"> (Inativo)</span>
                    <?php endif; ?>
                  </td>
                  <td><?= e($prod['category_name']) ?></td>
                  <td><?= e($prod['color_name']) ?> <small style="color:var(--gray)">(<?= e($prod['color_group']) ?>)</small></td>
                  <td><?= e(format_brl($prod['price'])) ?></td>
                  <td>
                    <div class="row-actions">
                      <a href="admin-produtos.php?editar=<?= (int)$prod['id'] ?>">Editar</a>
                      <form action="admin-produtos.php" method="post" onsubmit="return confirm('Excluir este produto?');" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$prod['id'] ?>">
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
        <h2><?= $editing ? 'Editar produto' : 'Novo produto' ?></h2>
        <form class="auth-form" action="admin-produtos.php" method="post" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="id" value="<?= (int)($editing['id'] ?? 0) ?>">

          <label class="field">
            <span>Nome do produto *</span>
            <input type="text" name="name" value="<?= e($editing['name'] ?? '') ?>" placeholder="Ex.: Gravata Masculina Social Slim Azul Marinho" required>
          </label>

          <label class="field">
            <span>Categoria *</span>
            <select name="category_id" required>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>" <?= ((int)($editing['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>>
                  <?= e($cat['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </label>

          <label class="field">
            <span>Nome da cor *</span>
            <input type="text" name="color_name" value="<?= e($editing['color_name'] ?? '') ?>" placeholder="Ex.: Azul Marinho" required>
          </label>

          <label class="field">
            <span>Filtro de tom *</span>
            <select name="color_group" required>
              <?php foreach ($colorGroups as $cg): ?>
                <option value="<?= e($cg) ?>" <?= (($editing['color_group'] ?? '') === $cg) ? 'selected' : '' ?>>
                  <?= e($cg) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </label>

          <label class="field">
            <span>Preço promocional (R$) *</span>
            <input type="number" step="0.01" min="0.01" name="price" value="<?= e((string)($editing['price'] ?? '18.13')) ?>" required>
          </label>

          <label class="field">
            <span>Preço original (R$)</span>
            <input type="number" step="0.01" min="0" name="old_price" value="<?= e((string)($editing['old_price'] ?? '25.90')) ?>">
          </label>

          <label class="field">
            <span>Enviar nova foto (JPG/PNG/WEBP)</span>
            <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp" style="padding-top:11px;">
          </label>

          <label class="field">
            <span>Ou caminho da imagem *</span>
            <input type="text" name="image_path" value="<?= e($editing['image'] ?? 'assets/produtos/gravata-azul-quartzo.jpg') ?>" required>
          </label>

          <label class="field">
            <span>Link de compra (Shopee / Mercado Livre)</span>
            <input type="url" name="purchase_url" value="<?= e($editing['purchase_url'] ?? 'https://shopee.com.br/savimaresociale') ?>">
          </label>

          <label class="field">
            <span>Descrição</span>
            <textarea name="description"><?= e($editing['description'] ?? 'Gravata social slim para casamentos, padrinhos e eventos formais.') ?></textarea>
          </label>

          <label class="field">
            <span>Vendidos / Avaliação</span>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
              <input type="number" min="0" name="sold_count" value="<?= (int)($editing['sold_count'] ?? 0) ?>" placeholder="Vendidos">
              <input type="number" step="0.1" min="1" max="5" name="rating" value="<?= e((string)($editing['rating'] ?? '5.0')) ?>" placeholder="Nota">
            </div>
          </label>

          <label class="checkbox-field">
            <input type="checkbox" name="is_best_seller" <?= !empty($editing['is_best_seller']) ? 'checked' : '' ?>>
            <span>Destacar em "Mais vendidas"</span>
          </label>

          <label class="checkbox-field">
            <input type="checkbox" name="active" <?= !isset($editing) || !empty($editing['active']) ? 'checked' : '' ?>>
            <span>Produto ativo no catálogo</span>
          </label>

          <button type="submit" class="button button-dark auth-submit">
            <?= $editing ? 'Salvar alterações' : 'Cadastrar produto' ?>
          </button>
          <?php if ($editing): ?>
            <a href="admin-produtos.php" class="button button-light" style="width:100%;">Cancelar edição</a>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </main>

  <?php require __DIR__ . '/includes/footer.php'; ?>
</body>

</html>