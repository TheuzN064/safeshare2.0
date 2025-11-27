<?php
require_once 'db.php';
requireLogin($pdo);
$currentPage = 'cartoes';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_card') {
        $stmt = $pdo->prepare('INSERT INTO cartoes (id_usuario, id_banco, titular, numero, validade, cvv, bandeira) VALUES (:user, :banco, :titular, :numero, :validade, :cvv, :bandeira)');
        $stmt->execute([
            'user' => $userId,
            'banco' => $_POST['id_banco'] ?? null,
            'titular' => $_POST['titular'] ?? '',
            'numero' => $_POST['numero'] ?? '',
            'validade' => $_POST['validade'] ?? '',
            'cvv' => $_POST['cvv'] ?? '',
            'bandeira' => $_POST['bandeira'] ?? '',
        ]);
        addLog($pdo, $userId, 'CARTAO_ADICIONADO', 'Cartão ' . ($_POST['bandeira'] ?? '') . ' vinculado a banco ID ' . ($_POST['id_banco'] ?? ''));
        header('Location: cartoes.php');
        exit;
    }

    if ($action === 'delete_card' && isset($_POST['id'])) {
        $stmt = $pdo->prepare('DELETE FROM cartoes WHERE id = :id AND id_usuario = :user');
        $stmt->execute(['id' => $_POST['id'], 'user' => $userId]);
        addLog($pdo, $userId, 'CARTAO_EXCLUIDO', 'Cartão ID ' . ($_POST['id']));
        header('Location: cartoes.php');
        exit;
    }
}

$bancos = $pdo->query('SELECT * FROM bancos ORDER BY nome')->fetchAll();
$sql = 'SELECT c.*, b.nome AS banco_nome FROM cartoes c INNER JOIN bancos b ON c.id_banco = b.id WHERE c.id_usuario = :user ORDER BY c.id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute(['user' => $userId]);
$cartoes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vault | Cartões</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">
    <?php include 'navbar.php'; ?>
    <main class="main">
        <div class="header">
            <h2>Cartões</h2>
            <a class="button secondary" href="bancos.php">Gerenciar Bancos</a>
        </div>

        <section class="card">
            <h3>Novo cartão</h3>
            <form method="POST" class="form-grid">
                <input type="hidden" name="action" value="add_card">
                <div>
                    <label>Titular</label>
                    <input class="input" name="titular" required>
                </div>
                <div>
                    <label>Número</label>
                    <input class="input" name="numero" required>
                </div>
                <div>
                    <label>Validade</label>
                    <input class="input" name="validade" placeholder="MM/AA" required>
                </div>
                <div>
                    <label>CVV</label>
                    <input class="input" name="cvv" required>
                </div>
                <div>
                    <label>Bandeira</label>
                    <input class="input" name="bandeira" required>
                </div>
                <div>
                    <label>Banco</label>
                    <select name="id_banco" required>
                        <option value="">Selecione</option>
                        <?php foreach ($bancos as $banco): ?>
                            <option value="<?= $banco['id'] ?>"><?= htmlspecialchars($banco['nome']) ?> (<?= htmlspecialchars($banco['codigo_banco']) ?>)</option>
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
                <h3>Carteira digital</h3>
                <span class="tag">cartoes.id_banco → bancos.id</span>
            </div>
            <div class="grid-3">
                <?php foreach ($cartoes as $cartao): ?>
                    <div class="card credit-card">
                        <div class="bank"><?= htmlspecialchars($cartao['banco_nome']) ?></div>
                        <div class="number"><?= htmlspecialchars($cartao['numero']) ?></div>
                        <div class="footer">
                            <div>
                                <div><?= htmlspecialchars($cartao['titular']) ?></div>
                                <small>Validade: <?= htmlspecialchars($cartao['validade']) ?></small>
                            </div>
                            <div class="tag"><?= htmlspecialchars($cartao['bandeira']) ?></div>
                        </div>
                        <form method="POST" style="margin-top:12px;" onsubmit="return confirm('Excluir cartão?');">
                            <input type="hidden" name="action" value="delete_card">
                            <input type="hidden" name="id" value="<?= $cartao['id'] ?>">
                            <button class="danger" type="submit">Excluir</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</div>
</body>
</html>
