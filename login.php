<?php
require_once 'db.php';

// Encerrar sessão
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if ($email === '' || $senha === '') {
        $errors[] = 'Informe e-mail e senha.';
    } else {
        $stmt = $pdo->prepare('SELECT id, nome, email FROM usuarios WHERE email = :email AND senha = :senha');
        $stmt->execute(['email' => $email, 'senha' => $senha]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'Credenciais inválidas. Use o usuário demo do script SQL.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vault | Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">
    <div class="auth-card">
        <h1>🔐 Vault</h1>
        <p class="muted">Entre com o usuário didático (senha em texto puro).</p>
        <?php if ($errors): ?>
            <div class="alert">
                <?php foreach ($errors as $error): ?>
                    <div><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="POST" class="form-grid">
            <div>
                <label>E-mail</label>
                <input class="input" type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div>
                <label>Senha (texto puro)</label>
                <input class="input" type="password" name="senha" required>
            </div>
            <div>
                <button type="submit" class="button" style="width:100%">Entrar</button>
            </div>
        </form>
        <small class="muted" style="display:block;margin-top:12px;">Usuário demo do script: demo@vault.test / 123456</small>
    </div>
</body>
</html>
