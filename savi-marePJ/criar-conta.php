<?php
require_once __DIR__ . '/includes/bootstrap.php';
ensure_default_admin();

$error = null;
$message = take_flash();
$old = ['nome' => '', 'email' => '', 'telefone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
  $name = trim((string)($_POST['nome'] ?? ''));
  $email = trim(strtolower(trim((string)($_POST['email'] ?? ''))));
  $phone = trim((string)($_POST['telefone'] ?? ''));
  $password = (string)($_POST['senha'] ?? '');
  $confirm = (string)($_POST['confirmar_senha'] ?? ($_POST['confirmar-senha'] ?? ''));
  $accepted = isset($_POST['termos']);

  $old = ['nome' => $name, 'email' => $email, 'telefone' => $phone];

  if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Informe seu nome e um e-mail válido.';
  } elseif (strlen($password) < 6) {
    $error = 'A senha deve ter pelo menos 6 caracteres.';
  } elseif ($password !== $confirm) {
    $error = 'As senhas não coincidem.';
  } elseif (!$accepted) {
    $error = 'É necessário aceitar os termos para criar a conta.';
  } else {
    try {
      $stmt = db()->prepare(
        "INSERT INTO users (name, email, phone, password_hash, role)
                 VALUES (:name, :email, :phone, :password_hash, 'customer')"
      );
      $stmt->execute([
        'name' => $name,
        'email' => $email,
        'phone' => $phone !== '' ? $phone : null,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
      ]);
      flash('success', 'Conta criada com sucesso. Faça login para continuar.');
      header('Location: login.php');
      exit;
    } catch (PDOException $exception) {
      if ($exception->getCode() === '23000') {
        $error = 'Este e-mail já está cadastrado.';
      } else {
        error_log($exception->getMessage());
        $error = 'Não foi possível criar sua conta agora. Tente novamente.';
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Criar conta | Savi Mare Sociale</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <link rel="icon" type="image/png" href="assets/logo2.png">
</head>

<body>

  <?php require __DIR__ . '/includes/header.php'; ?>

  <main>
    <section class="auth-section">
      <div class="auth-card">
        <div class="section-label">Nova conta</div>
        <h1 class="auth-title">Crie sua conta</h1>
        <p class="auth-subtitle">
          Leva menos de um minuto e você ganha 10% de desconto na primeira compra.
        </p>

        <form class="auth-form" action="criar-conta.php" method="post">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <?php if ($message): ?><p class="notice <?= e($message['type']) ?>" role="status"><?= e($message['message']) ?></p><?php endif; ?>
          <?php if ($error): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>

          <label class="field">
            <span>Nome completo</span>
            <input type="text" name="nome" value="<?= e($old['nome']) ?>" placeholder="Seu nome completo" required>
          </label>

          <label class="field">
            <span>E-mail</span>
            <input type="email" name="email" value="<?= e($old['email']) ?>" placeholder="seu@email.com" required>
          </label>

          <label class="field">
            <span>Telefone</span>
            <input type="tel" name="telefone" value="<?= e($old['telefone']) ?>" placeholder="(11) 90000-0000">
          </label>

          <label class="field">
            <span>Senha</span>
            <input type="password" name="senha" placeholder="Crie uma senha" minlength="6" required>
          </label>

          <label class="field">
            <span>Confirmar senha</span>
            <input type="password" name="confirmar_senha" placeholder="Repita a senha" minlength="6" required>
          </label>

          <label class="checkbox-field">
            <input type="checkbox" name="termos" required>
            <span>Aceito os termos de uso e a política de privacidade.</span>
          </label>

          <button type="submit" class="button button-dark auth-submit">Criar conta</button>
        </form>

        <p class="auth-footer-text">
          Já tem uma conta? <a href="login.php">Entrar</a>
        </p>
      </div>
    </section>
  </main>

  <?php require __DIR__ . '/includes/footer.php'; ?>
  <script src="assets/js/auth.js" defer></script>
</body>

</html>