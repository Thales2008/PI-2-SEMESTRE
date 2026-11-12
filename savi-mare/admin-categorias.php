<?php require_once __DIR__ . '/includes/bootstrap.php'; require_admin(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Categorias | Savi Mare Sociale</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<header>
<a href="index.html" class="logo">Savi Mare <span>Sociale</span></a>
<nav>
<a href="admin-produtos.php">Produtos</a>
<a href="admin-categorias.php">Categorias</a>
</nav>
<form action="logout.php" method="post" style="display:inline;margin:0">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<button type="submit" class="header-button" style="background:none;color:inherit;font-family:inherit;cursor:pointer">Sair</button>
</form>
</header>
<main>
<section class="admin">
<div class="admin-top"><div><div class="section-label">Painel</div><h1 class="section-title">Categorias</h1></div><a href="#form" class="button button-dark">Nova categoria</a></div>
<div class="admin-grid">
<div class="panel"><h2>Categorias cadastradas</h2>
<div class="table-wrap"><table class="table"><thead><tr><th>Nome</th><th>Descrição</th><th>Produtos</th><th>Ações</th></tr></thead><tbody><tr><td>Gravatas slim</td><td>Gravatas masculinas sociais de corte slim</td><td>12</td><td class="row-actions"><a href="#form">Editar</a></td></tr><tr><td>Kits para padrinhos</td><td>Kits com várias quantidades e cores</td><td>4</td><td class="row-actions"><a href="#form">Editar</a></td></tr><tr><td>Camisas sociais</td><td>Camisas sociais masculinas</td><td>3</td><td class="row-actions"><a href="#form">Editar</a></td></tr></tbody></table></div>
</div>
<div class="panel" id="form"><h2>Cadastrar categoria</h2>
<form class="auth-form" action="#" method="post">
<label class="field"><span>Nome</span><input type="text" name="nome" required></label>
<label class="field"><span>Descrição</span><textarea name="descricao"></textarea></label>
<button type="submit" class="button button-dark auth-submit">Salvar categoria</button>
</form></div>
</div>
</section>
</main>
<footer>
<div class="footer-bottom">
<span>© 2026 Savi Mare Sociale</span>
<span>Gravatas sociais slim</span>
</div>
</footer>
</body>
</html>
