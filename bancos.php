<?php
require_once 'db.php';
requireLogin($pdo);
$currentPage = 'bancos';
$alertRestrict = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add_banco') {
        $stmt = $pdo->prepare('INSERT INTO bancos (nome, codigo_banco) VALUES (:nome, :codigo)');
        $stmt->execute([
            'nome' => $_POST['nome'] ?? '',
            'codigo' => $_POST['codigo_banco'] ?? '',
        ]);
        addLog($pdo, $userId, 'BANCO_CRIADO', 'Banco "' . ($_POST['nome'] ?? '') . '" cadastrado');
        header('Location: bancos.php');
        exit;
    }

    if ($action === 'delete_banco' && isset($_POST['id'])) {
        try {
            $stmt = $pdo->prepare('DELETE FROM bancos WHERE id = :id');
            $stmt->execute(['id' => $_POST['id']]);
            addLog($pdo, $userId, 'BANCO_EXCLUIDO', 'Banco ID ' . ($_POST['id']));
            header('Location: bancos.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                // ON DELETE RESTRICT acionado
                $alertRestrict = true;
            } else {
                throw $e;
            }
        }
    }
}

$bancos = $pdo->query('SELECT * FROM bancos ORDER BY nome')->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vault | Bancos</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">
    <?php include 'navbar.php'; ?>
    <main class="main">
        <div class="header">
            <h2>Bancos</h2>
            <a class="button secondary" href="cartoes.php">Ver Cartões</a>
        </div>

        <section class="card">
            <div class="header">
                <h3>Adicionar banco</h3>
            </div>
            <form method="POST" class="form-grid">
                <input type="hidden" name="action" value="add_banco">
                <div>
                    <label>Nome</label>
                    <input class="input" name="nome" required>
                </div>
                <div>
                    <label>Código do banco</label>
                    <input class="input" name="codigo_banco" required>
                </div>
                <div style="align-self:end;">
                    <button type="submit">Salvar</button>
                </div>
            </form>
        </section>

        <section class="card">
            <div class="header">
                <h3>Lista de bancos</h3>
                <span class="tag">ON DELETE RESTRICT ativo</span>
            </div>
            <table class="table">
                <thead>
                    <tr><th>Nome</th><th>Código</th><th>Ações</th></tr>
                </thead>
                <tbody>
                <?php foreach ($bancos as $banco): ?>
                    <tr>
                        <td><?= htmlspecialchars($banco['nome']) ?></td>
                        <td><?= htmlspecialchars($banco['codigo_banco']) ?></td>
                        <td>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Deseja excluir este banco?');">
                                <input type="hidden" name="action" value="delete_banco">
                                <input type="hidden" name="id" value="<?= $banco['id'] ?>">
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
<?php if ($alertRestrict): ?>
<script>alert('Atenção: Não é possível excluir este banco pois existem cartões vinculados a ele (Regra ON DELETE RESTRICT).');</script>
<?php endif; ?>
</body>
</html>
