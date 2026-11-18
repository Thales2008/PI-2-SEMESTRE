<?php
$user = current_user();
$isHome = basename($_SERVER['PHP_SELF']) === 'index.php';
?>
<header>
  <a href="index.php" class="logo">
    Savi Mare <span>Sociale</span>
  </a>

  <button type="button" class="menu-toggle" aria-label="Abrir menu" aria-expanded="false">☰</button>

  <nav id="main-nav">
    <a href="<?= $isHome ? '#gravatas' : 'index.php#gravatas' ?>">Gravatas</a>
    <a href="catalogo.php">Catálogo</a>
    <a href="<?= $isHome ? '#mais-vendidas' : 'index.php#mais-vendidas' ?>">Mais vendidas</a>
    <a href="sobre.php">Sobre</a>
    <?php if (is_admin()): ?>
      <a href="admin-produtos.php">Painel Admin</a>
    <?php endif; ?>
  </nav>

  <div class="header-actions">
    <?php if ($user): ?>
      <span class="header-link">Olá, <?= e(explode(' ', trim($user['name']))[0]) ?></span>
      <form action="logout.php" method="post" class="inline-logout">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <button type="submit" class="header-button" style="background:transparent;cursor:pointer;font-family:inherit;">Sair</button>
      </form>
    <?php else: ?>
      <a href="login.php" class="header-link">Entrar</a>
      <a href="criar-conta.php" class="header-button">Criar conta</a>
    <?php endif; ?>
  </div>
</header>