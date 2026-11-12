<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/../config/database.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    $stored = $_SESSION['csrf_token'] ?? '';

    if (!is_string($sent) || !is_string($stored) || $stored === '' || !hash_equals($stored, $sent)) {
        http_response_code(419);
        exit('Sua sessão expirou. Atualize a página e tente novamente.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function take_flash(): ?array
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($message) ? $message : null;
}

/**
 * Cria a conta administrativa inicial somente se ela ainda não existir.
 * Credenciais solicitadas para esta primeira versão: usuário admin / senha admin.
 * Troque essa senha antes de disponibilizar o sistema publicamente.
 */
function ensure_default_admin(): void
{
    $pdo = db();
    $check = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
    $check->execute(['username' => 'admin']);

    if (!$check->fetch()) {
        $insert = $pdo->prepare(
            'INSERT INTO users (name, username, email, password_hash, role)
             VALUES (:name, :username, :email, :password_hash, :role)'
        );
        $insert->execute([
            'name' => 'Administrador',
            'username' => 'admin',
            'email' => 'admin@savimaresociale.local',
            'password_hash' => password_hash('admin', PASSWORD_DEFAULT),
            'role' => 'admin',
        ]);
    }
}

function current_user(): ?array
{
    return isset($_SESSION['user']) && is_array($_SESSION['user']) ? $_SESSION['user'] : null;
}

function require_admin(): void
{
    $user = current_user();
    if (!$user || ($user['role'] ?? '') !== 'admin') {
        flash('error', 'Entre com uma conta administrativa para acessar o painel.');
        header('Location: admin-login.php');
        exit;
    }
}

function require_customer(): void
{
    $user = current_user();
    if (!$user || ($user['role'] ?? '') !== 'customer') {
        flash('error', 'Entre na sua conta para continuar.');
        header('Location: login.php');
        exit;
    }
}
