<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Entrar | Savi Mare Sociale</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <header>

        <a href="index.html" class="logo">
            Savi Mare <span>Sociale</span>
        </a>

        <nav>
            <a href="index.html#gravatas">Gravatas</a>
            <a href="index.html#mais-vendidas">Mais vendidas</a>
            <a href="sobre.html">Sobre</a>
        </nav>

        <a href="criar-conta.php" class="header-button">
            Criar conta
        </a>

    </header>


    <main>

        <section class="auth-section">

            <div class="auth-card">

                <div class="section-label">
                    Bem-vindo de volta
                </div>

                <h1 class="auth-title">
                    Entrar
                </h1>

                <p class="auth-subtitle">
                    Acesse sua conta para acompanhar pedidos e favoritos.
                </p>

                <?php
require_once __DIR__ . '/includes/bootstrap.php';
ensure_default_admin();
$error = null;
$message = take_flash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['senha'] ?? '');

    $stmt = db()->prepare("SELECT id, name, email, password_hash, role FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $account = $stmt->fetch();

    if ($account && $account['role'] === 'customer' && password_verify($password, $account['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int)$account['id'],
            'name' => $account['name'],
            'email' => $account['email'],
            'role' => $account['role'],
        ];
        header('Location: index.html');
        exit;
    }
    $error = 'E-mail ou senha incorretos.';
}
?> 
<form class="auth-form" action="login.php" method="post">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<?php if ($message): ?><p class="notice <?= e($message['type']) ?>" role="status"><?= e($message['message']) ?></p><?php endif; ?>
<?php if ($error): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>

                    <label class="field">
                        <span>E-mail</span>
                        <input
                            type="email"
                            name="email"
                            placeholder="seu@email.com"
                            required>
                    </label>

                    <label class="field">
                        <span>Senha</span>
                        <input
                            type="password"
                            name="senha"
                            placeholder="Sua senha"
                            minlength="6"
                            required>
                    </label>

                    <div class="auth-row">

                        <label class="checkbox-field checkbox-field-inline">
                            <input type="checkbox" name="lembrar">
                            <span>Lembrar de mim</span>
                        </label>

                        <a href="#" class="auth-link">
                            Esqueceu a senha?
                        </a>

                    </div>

                    <button type="submit" class="button button-dark auth-submit">
                        Entrar
                    </button>

                </form>

                <p class="auth-footer-text">
                    Ainda não tem conta?
                    <a href="criar-conta.php">Criar conta</a>
                </p>

            </div>

        </section>

    </main>


    <footer>

        <div class="footer-bottom">

            <span>
                © 2026 Savi Mare Sociale
            </span>

            <span>
                Gravatas sociais slim
            </span>

        </div>

    </footer>

<script src="assets/js/auth.js" defer></script>\n</body>

</html>
