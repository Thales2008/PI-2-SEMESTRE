<?php
require_once __DIR__ . '/includes/bootstrap.php';
ensure_default_admin();
seed_initial_catalog();

$pdo = db();
$slug = trim((string)($_GET['p'] ?? ''));
$id = (int)($_GET['id'] ?? 0);

if ($slug !== '') {
  $stmt = $pdo->prepare(
    'SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM products p
         INNER JOIN categories c ON c.id = p.category_id
         WHERE p.slug = :slug AND p.active = 1 LIMIT 1'
  );
  $stmt->execute(['slug' => $slug]);
  $product = $stmt->fetch();
} elseif ($id > 0) {
  $stmt = $pdo->prepare(
    'SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM products p
         INNER JOIN categories c ON c.id = p.category_id
         WHERE p.id = :id AND p.active = 1 LIMIT 1'
  );
  $stmt->execute(['id' => $id]);
  $product = $stmt->fetch();
} else {
  $product = $pdo->query(
    'SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM products p
         INNER JOIN categories c ON c.id = p.category_id
         WHERE p.active = 1 ORDER BY p.id ASC LIMIT 1'
  )->fetch();
}

if (!$product) {
  http_response_code(404);
  flash('error', 'Produto não encontrado ou indisponível.');
  header('Location: catalogo.php');
  exit;
}

$allSwatches = $pdo->query(
  'SELECT id, name, slug, color_name, image FROM products WHERE active = 1 ORDER BY id ASC LIMIT 12'
)->fetchAll();

$stmtOther = $pdo->prepare(
  'SELECT p.*, c.name AS category_name
     FROM products p
     INNER JOIN categories c ON c.id = p.category_id
     WHERE p.active = 1 AND p.id != :id
     ORDER BY RAND() LIMIT 4'
);
$stmtOther->execute(['id' => $product['id']]);
$otherProducts = $stmtOther->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($product['name']) ?> | Savi Mare Sociale</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <link rel="icon" type="image/png" href="assets/logo2.png">
</head>

<body>

  <?php require __DIR__ . '/includes/header.php'; ?>

  <main>
    <section class="detail">
      <div class="detail-image">
        <img src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>">
      </div>

      <div>
        <p class="crumb">
          <a href="catalogo.php">Catálogo</a> /
          <a href="catalogo.php?categoria=<?= urlencode($product['category_slug']) ?>"><?= e($product['category_name']) ?></a>
        </p>

        <h1><?= e($product['name']) ?></h1>

        <div class="product-rating">
          <span class="stars">★★★★★</span>
          <?= number_format((float)$product['rating'], 1) ?>
          <?php if ((int)$product['sold_count'] > 0): ?>
            <span class="sold">· <?= (int)$product['sold_count'] ?> vendidos</span>
          <?php endif; ?>
        </div>

        <div class="price">
          <?= e(format_brl($product['price'])) ?>
          <?php if (!empty($product['old_price']) && (float)$product['old_price'] > (float)$product['price']): ?>
            <span class="old-price"><?= e(format_brl($product['old_price'])) ?></span>
          <?php endif; ?>
        </div>

        <p class="detail-text">
          <?= e($product['description'] ?: 'Gravata slim para casamentos, eventos formais e ocasiões especiais.') ?>
        </p>

        <div class="section-label" style="margin-top:28px">Cores disponíveis</div>
        <div class="swatches">
          <?php foreach ($allSwatches as $sw): ?>
            <a href="produto.php?p=<?= urlencode($sw['slug']) ?>"
              class="swatch <?= (int)$sw['id'] === (int)$product['id'] ? 'active' : '' ?>"
              title="<?= e($sw['color_name']) ?>">
              <img src="<?= e($sw['image']) ?>" alt="<?= e($sw['color_name']) ?>">
            </a>
          <?php endforeach; ?>
        </div>

        <div class="spec">
          <div>Categoria <b><?= e($product['category_name']) ?></b></div>
          <div>Cor selecionada <b><?= e($product['color_name']) ?></b></div>
          <div>Avaliação <b><?= number_format((float)$product['rating'], 1) ?> de 5</b></div>
        </div>

        <div class="hero-actions" style="margin-top:28px">
          <?php if (!empty($product['purchase_url'])): ?>
            <a href="<?= e($product['purchase_url']) ?>" target="_blank" rel="noopener noreferrer" class="button button-dark">
              Comprar na loja oficial
            </a>
          <?php endif; ?>
          <a href="catalogo.php" class="button button-light">Voltar ao catálogo</a>
        </div>
      </div>
    </section>

    <?php if ($otherProducts): ?>
      <section class="section" style="padding-top:20px">
        <div class="section-header">
          <h2 class="section-title">Outras cores</h2>
        </div>
        <div class="product-grid">
          <?php foreach ($otherProducts as $other): ?>
            <article class="product-card">
              <a href="produto.php?p=<?= urlencode($other['slug']) ?>">
                <div class="product-image">
                  <img src="<?= e($other['image']) ?>" alt="<?= e($other['name']) ?>" loading="lazy">
                </div>
                <div class="product-info">
                  <div class="product-category"><?= e($other['category_name']) ?></div>
                  <h3 class="product-name"><?= e($other['name']) ?></h3>
                </div>
              </a>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>
  </main>

  <?php require __DIR__ . '/includes/footer.php'; ?>
</body>

</html>