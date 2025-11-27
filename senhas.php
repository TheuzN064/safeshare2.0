<?php
require_once 'db.php';
requireLogin($pdo);

$currentPage = 'senhas';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add_login') {
        $stmt = $pdo->prepare('INSERT INTO logins (id_usuario, id_categoria, site_nome, site_url, login, senha) VALUES (:user, :categoria, :site_nome, :site_url, :login, :senha)');
        $stmt->execute([
            'user' => $userId,
            'categoria' => $_POST['id_categoria'] ?? null,
            'site_nome' => $_POST['site_nome'] ?? '',
            'site_url' => $_POST['site_url'] ?? '',
            'login' => $_POST['login'] ?? '',
            'senha' => $_POST['senha'] ?? '',
        ]);

        addLog($pdo, $userId, 'LOGIN_ADICIONADO', 'Login para ' . ($_POST['site_nome'] ?? ''));

        header('Location: senhas.php');
        exit;
    }

    if ($action === 'delete_login' && isset($_POST['id'])) {
        $stmt = $pdo->prepare('DELETE FROM logins WHERE id = :id AND id_usuario = :user');
        $stmt->execute(['id' => $_POST['id'], 'user' => $userId]);

        addLog($pdo, $userId, 'LOGIN_EXCLUIDO', 'Login ID ' . ($_POST['id']));

        header('Location: senhas.php');
        exit;
    }
}

$categorias = $pdo->query('SELECT * FROM categorias ORDER BY nome')->fetchAll();
$sql = 'SELECT l.*, c.nome AS categoria_nome, c.cor_hex FROM logins l INNER JOIN categorias c ON l.id_categoria = c.id WHERE l.id_usuario = :user ORDER BY l.id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute(['user' => $userId]);
$logins = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vault | Senhas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">
    <?php include 'navbar.php'; ?>
    <main class="main">
        <div class="header">
            <h2>Senhas</h2>
            <a class="button secondary" href="index.php">Voltar ao Dashboard</a>
        </div>

        <section class="card">
            <h3>Nova credencial</h3>
            <form method="POST" class="form-grid">
                <input type="hidden" name="action" value="add_login">
                <div>
                    <label>Site</label>
                    <input class="input" name="site_nome" required>
                </div>
                <div>
                    <label>URL</label>
                    <input class="input" name="site_url" type="url" placeholder="https://">
                </div>
                <div>
                    <label>Login</label>
                    <input class="input" name="login" required>
                </div>
                <div>
                    <label>Senha (texto puro)</label>
                    <input class="input" name="senha" required>
                </div>
                <div>
                    <label>Categoria</label>
                    <select name="id_categoria" required>
                        <option value="">Selecione</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="align-self:end;">
                    <button type="submit">Salvar</button>
                </div>
            </form>
        </section>

        <section class="card">
            <div class="header">
                <h3>Logins salvos</h3>
                <span class="tag">INNER JOIN categorias</span>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Site</th>
                        <th>Login</th>
                        <th>Senha</th>
                        <th>Categoria</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($logins as $login): ?>
                    <tr>
                        <td>
                            <div><?= htmlspecialchars($login['site_nome']) ?></div>
                            <?php if (!empty($login['site_url'])): ?>
                                <small class="muted"><?= htmlspecialchars($login['site_url']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($login['login']) ?></td>
                        <td>
                            <div class="flex">
                                <input type="password" class="input" value="<?= htmlspecialchars($login['senha']) ?>" data-password readonly style="max-width:180px;">
                                <button type="button" class="button secondary" onclick="togglePassword(this)">👁</button>
                                <button type="button" class="button secondary" onclick="copyPassword('<?= htmlspecialchars($login['senha']) ?>')">📋</button>
                            </div>
                        </td>
                        <td>
                            <span class="badge">
                                <span class="dot" style="background: <?= htmlspecialchars($login['cor_hex']) ?>"></span>
                                <?= htmlspecialchars($login['categoria_nome']) ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Excluir login?');">
                                <input type="hidden" name="action" value="delete_login">
                                <input type="hidden" name="id" value="<?= $login['id'] ?>">
                                <button class="danger" type="submit">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

<script>
function togglePassword(btn) {
    const input = btn.parentElement.querySelector('[data-password]');
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
}

function copyPassword(value) {
    navigator.clipboard.writeText(value).then(() => {
        alert('Senha copiada para a área de transferência.');
    });
}
</script>
</body>
</html>
