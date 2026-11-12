<?php require_once __DIR__ . '/includes/bootstrap.php'; require_admin(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Produtos | Savi Mare Sociale</title>
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
<div class="admin-top"><div><div class="section-label">Painel</div><h1 class="section-title">Produtos</h1></div><a href="#form" class="button button-dark">Novo produto</a></div>
<p class="notice" role="status">Produto salvo com sucesso.</p>
<div class="admin-grid">
<div class="panel"><h2>Produtos cadastrados</h2>
<div class="table-wrap"><table class="table"><thead><tr><th>Foto</th><th>Nome</th><th>Categoria</th><th>Preço</th><th>Ações</th></tr></thead><tbody><tr><td><img src="assets/rose-canela.png" alt=""></td><td>Gravata Slim Rosê Claro Premium</td><td>Social Slim</td><td>R$ 18,13</td><td class="row-actions"><a href="#form">Editar</a><a href="#" class="danger">Excluir</a></td></tr><tr><td><img src="assets/vermelho.png" alt=""></td><td>Gravata Slim Vermelho</td><td>Social Slim</td><td>R$ 18,13</td><td class="row-actions"><a href="#form">Editar</a><a href="#" class="danger">Excluir</a></td></tr><tr><td><img src="assets/azul_royal.png" alt=""></td><td>Gravata Slim Azul Royal</td><td>Social Slim</td><td>R$ 18,13</td><td class="row-actions"><a href="#form">Editar</a><a href="#" class="danger">Excluir</a></td></tr><tr><td><img src="assets/verde.png" alt=""></td><td>Gravata Slim Verde</td><td>Social Slim</td><td>R$ 18,13</td><td class="row-actions"><a href="#form">Editar</a><a href="#" class="danger">Excluir</a></td></tr></tbody></table></div>
</div>
<div class="panel" id="form"><h2>Cadastrar produto</h2>
<form class="auth-form" action="#" method="post">
<label class="field"><span>Nome</span><input type="text" name="nome" required></label>
<label class="field"><span>Categoria</span><select name="categoria" required><option value="">Selecione</option><option>Gravatas slim</option><option>Kits para padrinhos</option><option>Camisas sociais</option></select></label>
<label class="field"><span>Preço (R$)</span><input type="number" name="preco" min="0" step="0.01" required></label>
<label class="field"><span>Descrição</span><textarea name="descricao" required></textarea></label>
<label class="field"><span>Imagem</span><input type="file" name="imagem" accept="image/*"></label>
<label class="checkbox-field checkbox-field-inline"><input type="checkbox" name="ativo" checked><span>Mostrar no catálogo</span></label>
<button type="submit" class="button button-dark auth-submit">Salvar produto</button>
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
