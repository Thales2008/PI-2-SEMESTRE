<?php
require_once __DIR__ . '/includes/bootstrap.php';
ensure_default_admin();
seed_initial_catalog();

$pdo = db();
$categories = $pdo->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();

$selectedCategory = trim((string)($_GET['categoria'] ?? ''));
$selectedColor = trim((string)($_GET['cor'] ?? 'Todas'));

$sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM products p
        INNER JOIN categories c ON c.id = p.category_id
        WHERE p.active = 1';
$params = [];

if ($selectedCategory !== '') {
  $sql .= ' AND c.slug = :cat';
  $params['cat'] = $selectedCategory;
}

$sql .= ' ORDER BY p.id ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$colorGroups = ['Todas', 'Neutras', 'Verdes', 'Rosas', 'Azuis', 'Marrons', 'Outras cores'];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Catálogo completo | Savi Mare Sociale</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <link rel="icon" type="image/png" href="assets/logo2.png">
</head>

<body>

  <?php require __DIR__ . '/includes/header.php'; ?>

  <main>
    <section class="section catalog-section" id="catalogo">
      <div class="section-header">
        <div>
          <div class="section-label">Coleção completa</div>
          <h1 class="section-title">Catálogo</h1>
        </div>
        <p class="section-description">
          Todas as gravatas e acessórios disponíveis na Savi Mare Sociale, em um só lugar.
        </p>
      </div>

      <div class="filters" style="border-bottom:none;padding-bottom:10px;margin-bottom:10px;">
        <a href="catalogo.php" class="filter <?= $selectedCategory === '' ? 'active' : '' ?>">Todas as categorias</a>
        <?php foreach ($categories as $cat): ?>
          <a href="catalogo.php?categoria=<?= urlencode($cat['slug']) ?>"
            class="filter <?= $selectedCategory === $cat['slug'] ? 'active' : '' ?>">
            <?= e($cat['name']) ?>
          </a>
        <?php endforeach; ?>
      </div>

      <div class="filters" data-filter-bar>
        <?php foreach ($colorGroups as $group): ?>
          <button type="button"
            class="filter <?= $selectedColor === $group ? 'active' : '' ?>"
            data-color="<?= e($group) ?>">
            <?= e($group) ?>
          </button>
        <?php endforeach; ?>

        <select class="sort" aria-label="Ordenar catálogo">
          <option value="default">Ordenar por</option>
          <option value="sold">Mais vendidos</option>
          <option value="rating">Maior avaliação</option>
          <option value="price-asc">Menor preço</option>
          <option value="price-desc">Maior preço</option>
        </select>
      </div>

      <?php if (!$products): ?>
        <p class="detail-text">Nenhum produto encontrado nesta categoria.</p>
      <?php else: ?>
        <div class="product-grid" data-product-grid>
          <?php foreach ($products as $p): ?>
            <article class="product-card"
              data-color="<?= e($p['color_group']) ?>"
              data-category="<?= e($p['category_name']) ?>"
              data-price="<?= e((string)$p['price']) ?>"
              data-rating="<?= e((string)$p['rating']) ?>"
              data-sold="<?= e((string)$p['sold_count']) ?>">
              <div class="product-image">
                <?php if ((int)$p['discount_percent'] > 0): ?>
                  <span class="discount">-<?= (int)$p['discount_percent'] ?>%</span>
                <?php endif; ?>
                <a href="produto.php?p=<?= urlencode($p['slug']) ?>">
                  <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                </a>
              </div>

              <div class="product-info">
                <div class="product-category"><?= e($p['category_name']) ?></div>
                <h3 class="product-name">
                  <a href="produto.php?p=<?= urlencode($p['slug']) ?>"><?= e($p['name']) ?></a>
                </h3>

                <div class="product-rating">
                  <span class="stars">★★★★★</span>
                  <?= number_format((float)$p['rating'], 1) ?>
                  <?php if ((int)$p['sold_count'] > 0): ?>
                    <span class="sold">· <?= (int)$p['sold_count'] ?> vendidos</span>
                  <?php endif; ?>
                </div>

                <div class="product-bottom">
                  <div class="price">
                    <?= e(format_brl($p['price'])) ?>
                    <?php if (!empty($p['old_price']) && (float)$p['old_price'] > (float)$p['price']): ?>
                      <span class="old-price"><?= e(format_brl($p['old_price'])) ?></span>
                    <?php endif; ?>
                  </div>
                  <a href="produto.php?p=<?= urlencode($p['slug']) ?>" class="buy" aria-label="Ver <?= e($p['name']) ?>">→</a>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <section class="cta">
      <div class="cta-label">Ainda não achou a sua?</div>
      <h2>Crie sua conta e receba novidades em primeira mão.</h2>
      <p>Cadastre-se e ganhe 10% de desconto na primeira compra.</p>
      <a href="criar-conta.php" class="cta-button">Criar conta</a>
    </section>
  </main>

  <?php require __DIR__ . '/includes/footer.php'; ?>
</body>

</html>