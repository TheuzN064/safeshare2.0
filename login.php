<?php
require_once 'db.php';

// Encerrar sessão
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$errors = [];
$mode = $_GET['mode'] ?? 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? 'login';
    $mode = $formType === 'register' ? 'register' : 'login';

    if ($formType === 'login') {
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
                addLog($pdo, $user['id'], 'LOGIN_SUCESSO', 'Usuário autenticado no painel');
                header('Location: index.php');
                exit;
            } else {
                $errors[] = 'Credenciais inválidas. Use o usuário demo do script SQL ou cadastre-se.';
            }
        }
    }

    if ($formType === 'register') {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = trim($_POST['senha'] ?? '');

        if ($nome === '' || $email === '' || $senha === '') {
            $errors[] = 'Preencha nome, e-mail e senha.';
        } else {
            $exists = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE email = :email');
            $exists->execute(['email' => $email]);
            if ($exists->fetchColumn() > 0) {
                $errors[] = 'Já existe um usuário com este e-mail.';
            } else {
                $insert = $pdo->prepare('INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)');
                $insert->execute([
                    'nome' => $nome,
                    'email' => $email,
                    'senha' => $senha,
                ]);
                $newUserId = (int)$pdo->lastInsertId();
                $_SESSION['user_id'] = $newUserId;
                addLog($pdo, $newUserId, 'REGISTRO', 'Novo usuário registrado: ' . $nome);
                header('Location: index.php');
                exit;
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
    <title>Vault | Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">
    <div class="auth-card">
        <h1>🔐 Vault</h1>
        <div class="auth-switch">
            <a class="<?= $mode === 'login' ? 'active' : '' ?>" href="?mode=login">Entrar</a>
            <a class="<?= $mode === 'register' ? 'active' : '' ?>" href="?mode=register">Registrar</a>
        </div>
        <p class="muted">Senhas em texto puro para fins didáticos de relacionamento.</p>
        <?php if ($errors): ?>
            <div class="alert">
                <?php foreach ($errors as $error): ?>
                    <div><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($mode === 'login'): ?>
            <form method="POST" class="form-grid">
                <input type="hidden" name="form_type" value="login">
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
        <?php else: ?>
            <form method="POST" class="form-grid">
                <input type="hidden" name="form_type" value="register">
                <div>
                    <label>Nome</label>
                    <input class="input" name="nome" required value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
                </div>
                <div>
                    <label>E-mail</label>
                    <input class="input" type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div>
                    <label>Senha (texto puro)</label>
                    <input class="input" type="password" name="senha" required>
                </div>
                <div>
                    <button type="submit" class="button" style="width:100%">Criar conta e acessar</button>
                </div>
            </form>
            <small class="muted" style="display:block;margin-top:12px;">A conta será criada imediatamente (senha em texto puro para visualização no banco).</small>
        <?php endif; ?>
    </div>
</body>
</html>
