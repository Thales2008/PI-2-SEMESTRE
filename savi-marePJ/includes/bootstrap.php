<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $cfg = require __DIR__ . '/../config/database.php';
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $cfg['host'],
        $cfg['port'],
        $cfg['name'],
        $cfg['charset']
    );

    $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function format_brl(float|string|null $value): string
{
    return 'R$ ' . number_format((float)$value, 2, ',', '.');
}

function slugify(string $text): string
{
    $text = mb_strtolower(trim($text), 'UTF-8');
    $map = [
        'á'=>'a','à'=>'a','ã'=>'a','â'=>'a','ä'=>'a',
        'é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
        'í'=>'i','ì'=>'i','î'=>'i','ï'=>'i',
        'ó'=>'o','ò'=>'o','õ'=>'o','ô'=>'o','ö'=>'o',
        'ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u',
        'ç'=>'c','ñ'=>'n'
    ];
    $text = strtr($text, $map);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-') ?: 'item-' . bin2hex(random_bytes(3));
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
    $sent = (string)($_POST['csrf_token'] ?? '');
    $valid = (string)($_SESSION['csrf_token'] ?? '');
    if ($sent === '' || $valid === '' || !hash_equals($valid, $sent)) {
        http_response_code(403);
        exit('Requisição inválida (token CSRF). Recarregue a página e tente novamente.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function take_flash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $item = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $item;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_admin(): bool
{
    $user = current_user();
    return is_array($user) && ($user['role'] ?? '') === 'admin';
}

function require_admin(): void
{
    if (!is_admin()) {
        flash('error', 'Faça login como administrador para acessar o painel.');
        header('Location: admin-login.php');
        exit;
    }
}

function ensure_default_admin(): void
{
    try {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :u OR role = :r LIMIT 1');
        $stmt->execute(['u' => 'admin', 'r' => 'admin']);
        if (!$stmt->fetch()) {
            $ins = $pdo->prepare(
                'INSERT INTO users (name, username, email, password_hash, role)
                 VALUES (:name, :username, :email, :hash, :role)'
            );
            $ins->execute([
                'name' => 'Administrador Savi Mare',
                'username' => 'admin',
                'email' => 'admin@savimaresociale.com.br',
                'hash' => password_hash('admin', PASSWORD_DEFAULT),
                'role' => 'admin',
            ]);
        }
    } catch (Throwable $e) {
        error_log('ensure_default_admin: ' . $e->getMessage());
    }
}

function seed_initial_catalog(): void
{
    try {
        $pdo = db();
        $catCount = (int)$pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
        if ($catCount === 0) {
            $insCat = $pdo->prepare('INSERT INTO categories (name, slug, description) VALUES (:name, :slug, :desc)');
            $defaults = [
                ['Social Slim', 'social-slim', 'Gravatas masculinas sociais slim para casamentos e eventos formais.'],
                ['Padrinhos', 'padrinhos', 'Tons selecionados para padronização visual de padrinhos de casamento.'],
                ['Kits Padrinhos', 'kits-padrinhos', 'Conjuntos coordenados em diferentes quantidades e cores.'],
                ['Camisas Sociais', 'camisas-sociais', 'Camisas sociais masculinas Slim Fit para composição completa.'],
            ];
            foreach ($defaults as [$name, $slug, $desc]) {
                $insCat->execute(['name' => $name, 'slug' => $slug, 'desc' => $desc]);
            }
        }

        $prodCount = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
        if ($prodCount === 0) {
            $catId = (int)$pdo->query("SELECT id FROM categories WHERE slug = 'social-slim' LIMIT 1")->fetchColumn();
            if ($catId <= 0) {
                $catId = (int)$pdo->query('SELECT id FROM categories LIMIT 1')->fetchColumn();
            }

            $items = [
                ['Gravata Masculina Social Slim Amarelo Manteiga Premium', 'amarelo-manteiga', 'Amarelo Manteiga', 'Outras cores', 18.13, 25.90, 30, 4.9, 24, 'assets/produtos/gravata-amarelo-manteiga.jpg', 0, null],
                ['Gravata Masculina Social Slim Azul Quartzo Premium', 'azul-quartzo', 'Azul Quartzo', 'Azuis', 18.13, 25.90, 30, 4.9, 38, 'assets/produtos/gravata-azul-quartzo.jpg', 0, null],
                ['Gravata Masculina Social Slim Azul Serenity Escuro Premium', 'azul-serenity-escuro', 'Serenity Escuro', 'Azuis', 18.13, 25.90, 30, 5.0, 282, 'assets/produtos/gravata-azul-serenity-escuro.jpg', 1, 'Mais vendida'],
                ['Gravata Masculina Social Slim Cinza Chumbo Premium', 'cinza-chumbo', 'Cinza Chumbo', 'Neutras', 18.13, 25.90, 30, 4.9, 64, 'assets/produtos/gravata-cinza-chumbo.jpg', 0, null],
                ['Gravata Masculina Social Slim Fúcsia Escuro Premium', 'fucsia-escuro', 'Fúcsia Escuro', 'Rosas', 18.13, 25.90, 30, 4.8, 19, 'assets/produtos/gravata-fucsia-escuro.jpg', 0, null],
                ['Gravata Masculina Social Slim Laranja Terracota Premium', 'laranja-terracota', 'Laranja Terracota', 'Marrons', 18.13, 25.90, 30, 5.0, 95, 'assets/produtos/gravata-laranja-terracota.jpg', 0, null],
                ['Gravata Masculina Social Slim Marrom Café Claro Premium', 'marrom-cafe-claro', 'Marrom Café Claro', 'Marrons', 18.13, 25.90, 30, 4.9, 226, 'assets/produtos/gravata-marrom-cafe-claro.jpg', 1, 'Destaque'],
                ['Gravata Masculina Social Slim Marrom Premium', 'marrom', 'Marrom', 'Marrons', 18.13, 25.90, 30, 4.8, 72, 'assets/produtos/gravata-marrom.jpg', 0, null],
                ['Gravata Masculina Social Slim Marsala Premium', 'marsala', 'Marsala', 'Rosas', 18.13, 25.90, 30, 5.0, 140, 'assets/produtos/gravata-marsala.jpg', 0, null],
                ['Gravata Masculina Social Slim Salmão Premium', 'salmao', 'Salmão', 'Rosas', 18.13, 25.90, 30, 5.0, 100, 'assets/produtos/gravata-salmao.jpg', 1, 'Favorita'],
                ['Gravata Masculina Social Slim Verde Musgo Premium', 'verde-musgo', 'Verde Musgo', 'Verdes', 18.13, 25.90, 30, 4.9, 88, 'assets/produtos/gravata-verde-musgo.jpg', 0, null],
                ['Gravata Masculina Social Slim Xadrez Azul Marinho Premium', 'xadrez', 'Xadrez Azul Marinho', 'Azuis', 18.13, 25.90, 30, 4.9, 41, 'assets/produtos/gravata-xadrez.jpg', 0, null],
            ];

            $insProd = $pdo->prepare(
                'INSERT INTO products
                 (category_id, name, slug, color_name, color_group, description, price, old_price, discount_percent, rating, sold_count, image, purchase_url, is_best_seller, best_seller_label, active)
                 VALUES
                 (:cat, :name, :slug, :color, :cgroup, :desc, :price, :old, :disc, :rating, :sold, :img, :url, :best, :blabel, 1)'
            );

            foreach ($items as $item) {
                $insProd->execute([
                    'cat' => $catId,
                    'name' => $item[0],
                    'slug' => $item[1],
                    'color' => $item[2],
                    'cgroup' => $item[3],
                    'desc' => 'Gravata social slim confeccionada com acabamento premium para casamentos, padrinhos, formaturas e eventos formais.',
                    'price' => $item[4],
                    'old' => $item[5],
                    'disc' => $item[6],
                    'rating' => $item[7],
                    'sold' => $item[8],
                    'img' => $item[9],
                    'url' => 'https://shopee.com.br/savimaresociale',
                    'best' => $item[10],
                    'blabel' => $item[11],
                ]);
            }
        }
    } catch (Throwable $e) {
        error_log('seed_initial_catalog: ' . $e->getMessage());
    }
}