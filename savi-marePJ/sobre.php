<?php
require_once __DIR__ . '/includes/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sobre | Savi Mare Sociale</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <link rel="icon" type="image/png" href="assets/logo2.png">
</head>

<body>

  <?php require __DIR__ . '/includes/header.php'; ?>

  <main>
    <section class="about">
      <div class="section-label">Quem somos</div>
      <h2>Moda masculina social para momentos que merecem atenção aos detalhes</h2>
      <p>
        A Savi Mare Sociale vende gravatas masculinas slim, kits para padrinhos em diferentes quantidades e cores e camisas sociais, pensados para casamentos, eventos formais e ocasiões especiais.
      </p>
    </section>

    <section class="section" style="padding-top:0">
      <div class="info-grid">
        <div class="panel">
          <h2>Nossa proposta</h2>
          <p class="detail-text">Facilitar a escolha de produtos por meio de variedade e atendimento acessível, com um catálogo organizado por categorias.</p>
        </div>
        <div class="panel">
          <h2>Para quem</h2>
          <p class="detail-text">Noivos, padrinhos e homens que procuram acessórios para eventos sociais e buscam ver imagens, cores e modelos com rapidez.</p>
        </div>
        <div class="panel">
          <h2>Canais oficiais</h2>
          <p class="detail-text">
            <a class="auth-link" href="https://www.instagram.com/savi.maresociale/" target="_blank" rel="noopener noreferrer">Instagram</a>
            <a class="auth-link" href="https://shopee.com.br/savimaresociale" target="_blank" rel="noopener noreferrer">Shopee</a>
            <a class="auth-link" href="https://www.mercadolivre.com.br/pagina/bhcdbfeag1664" target="_blank" rel="noopener noreferrer">Mercado Livre</a>
          </p>
        </div>
      </div>
    </section>

    <section class="cta">
      <div class="cta-label">Catálogo</div>
      <h2>Veja todas as gravatas</h2>
      <p>Filtre por categoria e abra cada produto para ver detalhes e cores.</p>
      <a href="catalogo.php" class="cta-button">Ver catálogo</a>
    </section>
  </main>

  <?php require __DIR__ . '/includes/footer.php'; ?>
</body>

</html>