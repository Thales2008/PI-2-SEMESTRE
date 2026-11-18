<?php
require_once __DIR__ . '/includes/bootstrap.php';
ensure_default_admin();
seed_initial_catalog();

$pdo = db();
$products = $pdo->query(
  'SELECT p.*, c.name AS category_name
     FROM products p
     INNER JOIN categories c ON c.id = p.category_id
     WHERE p.active = 1
     ORDER BY p.id ASC'
)->fetchAll();

$bestSellers = $pdo->query(
  'SELECT * FROM products
     WHERE active = 1 AND is_best_seller = 1
     ORDER BY sold_count DESC
     LIMIT 3'
)->fetchAll();

if (!$bestSellers && $products) {
  $bestSellers = array_slice($products, 0, 3);
}

$flash = take_flash();
$colorGroups = ['Todas', 'Neutras', 'Verdes', 'Rosas', 'Azuis', 'Marrons', 'Outras cores'];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Savi Mare Sociale</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <link rel="icon" type="image/png" href="assets/logo2.png">
</head>

<body>

  <?php require __DIR__ . '/includes/header.php'; ?>

  <?php if ($flash): ?>
    <div style="max-width:1250px;margin:20px auto 0;padding:0 30px;">
      <p class="notice <?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></p>
    </div>
  <?php endif; ?>

  <section class="carousel" aria-label="Destaques">
    <button type="button" class="carousel-arrow prev" data-dir="-1" aria-label="Slide anterior">‹</button>

    <div class="carousel-track">
      <a href="#gravatas" id="slide-1" class="carousel-slide">
        <img src="assets/banner1.png" alt="Nova coleção de gravatas">
        <div class="carousel-caption">
          <span class="carousel-label">Nova coleção</span>
          <h2>Tons para cada estação</h2>
        </div>
      </a>

      <a href="criar-conta.php" id="slide-2" class="carousel-slide">
        <img src="assets/banner1.png" alt="Crie sua conta">
        <div class="carousel-caption">
          <span class="carousel-label">Novidade</span>
          <h2>Crie sua conta e ganhe 10% na primeira compra</h2>
        </div>
      </a>

      <a href="catalogo.php" id="slide-3" class="carousel-slide">
        <img src="assets/banner1.png" alt="Frete grátis">
        <div class="carousel-caption">
          <span class="carousel-label">Vantagem</span>
          <h2>Frete grátis em compras acima de R$ 150</h2>
        </div>
      </a>
    </div>

    <button type="button" class="carousel-arrow next" data-dir="1" aria-label="Próximo slide">›</button>
  </section>

  <main>
    <section class="hero">
      <div class="hero-content">
        <div class="eyebrow">Gravatas premium</div>
        <h1>Elegância nos <span>detalhes.</span></h1>
        <p class="hero-text">
          Gravatas sociais slim pensadas para casamentos, padrinhos, formaturas e ocasiões em que cada detalhe faz diferença.
        </p>
        <div class="hero-actions">
          <a href="#gravatas" class="button button-dark">Ver gravatas</a>
          <a href="catalogo.php" class="button button-light">Catálogo completo</a>
        </div>
      </div>

      <div class="hero-image">
        <img src="assets/destaque.png" alt="Coleção de gravatas sociais">
      </div>
    </section>

    <section class="section" id="gravatas">
      <div class="section-header">
        <div>
          <div class="section-label">Coleção</div>
          <h2 class="section-title">Gravatas</h2>
        </div>
        <p class="section-description">
          Nossa seleção de gravatas masculinas sociais slim, com diferentes tons para combinar com cada ocasião.
        </p>
      </div>

      <div class="filters" data-filter-bar>
        <?php foreach ($colorGroups as $group): ?>
          <button type="button" class="filter <?= $group === 'Todas' ? 'active' : '' ?>" data-color="<?= e($group) ?>">
            <?= e($group) ?>
          </button>
        <?php endforeach; ?>

        <select class="sort" aria-label="Ordenar produtos">
          <option value="default">Ordenar por</option>
          <option value="sold">Mais vendidos</option>
          <option value="rating">Maior avaliação</option>
          <option value="price-asc">Menor preço</option>
          <option value="price-desc">Maior preço</option>
        </select>
      </div>

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
                <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy" width="900" height="900">
              </a>
            </div>
            <div class="product-info">
              <div class="product-category"><?= e($p['category_name']) ?></div>
              <h3 class="product-name">
                <a href="produto.php?p=<?= urlencode($p['slug']) ?>"><?= e($p['name']) ?></a>
              </h3>
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
    </section>

    <section class="best-section" id="mais-vendidas">
      <div class="section">
        <div class="section-header">
          <div>
            <div class="section-label">Destaques</div>
            <h2 class="section-title">Mais vendidas</h2>
          </div>
          <p class="section-description">Os tons que mais se destacam entre os pedidos.</p>
        </div>

        <div class="best-grid">
          <?php foreach ($bestSellers as $idx => $best): ?>
            <a href="produto.php?p=<?= urlencode($best['slug']) ?>" class="best-card">
              <div class="best-image">
                <img src="<?= e($best['image']) ?>" alt="<?= e($best['name']) ?>" loading="lazy">
              </div>
              <div class="best-info">
                <div class="best-label"><?= e($best['best_seller_label'] ?: ($idx === 0 ? 'Mais vendida' : 'Destaque')) ?></div>
                <h3 class="best-name"><?= e($best['color_name']) ?></h3>
                <p class="best-sales"><?= (int)$best['sold_count'] ?> vendidos · ★ <?= number_format((float)$best['rating'], 1) ?></p>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section class="about" id="sobre">
      <div class="section-label">Savi Mare Sociale</div>
      <h2>O detalhe que transforma um traje.</h2>
      <p>
        Uma seleção dedicada exclusivamente às gravatas sociais slim. Cores clássicas, tons modernos e opções pensadas especialmente para casamentos, padrinhos e ocasiões especiais.
      </p>
    </section>

    <section class="cta">
      <div class="cta-label">Encontre sua gravata</div>
      <h2>Seu estilo começa nos detalhes.</h2>
      <p>Explore a coleção completa de gravatas e encontre o tom ideal para sua ocasião.</p>
      <a href="catalogo.php" class="cta-button">Ver coleção completa</a>
    </section>
  </main>

  <?php require __DIR__ . '/includes/footer.php'; ?>
</body>

</html>