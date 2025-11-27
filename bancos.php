<?php
require_once 'db.php';
requireLogin($pdo);
$currentPage = 'bancos';
$alertRestrict = false;
$editingBanco = null;

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

    if ($action === 'update_banco' && isset($_POST['id'])) {
        $stmt = $pdo->prepare('UPDATE bancos SET nome = :nome, codigo_banco = :codigo WHERE id = :id AND ativo = 1');
        $stmt->execute([
            'id' => $_POST['id'],
            'nome' => $_POST['nome'] ?? '',
            'codigo' => $_POST['codigo_banco'] ?? '',
        ]);
        addLog($pdo, $userId, 'BANCO_EDITADO', 'Banco ID ' . ($_POST['id']));
        header('Location: bancos.php');
        exit;
    }

    if ($action === 'delete_banco' && isset($_POST['id'])) {
        $hasCards = $pdo->prepare('SELECT COUNT(*) FROM cartoes WHERE id_banco = :id AND ativo = 1');
        $hasCards->execute(['id' => $_POST['id']]);
        if ($hasCards->fetchColumn() > 0) {
            $alertRestrict = true;
        } else {
            $stmt = $pdo->prepare('UPDATE bancos SET ativo = 0 WHERE id = :id');
            $stmt->execute(['id' => $_POST['id']]);
            addLog($pdo, $userId, 'BANCO_DESATIVADO', 'Banco ID ' . ($_POST['id']));
            header('Location: bancos.php');
            exit;
        }
    }

    if ($action === 'restore_banco' && isset($_POST['id'])) {
        $stmt = $pdo->prepare('UPDATE bancos SET ativo = 1 WHERE id = :id');
        $stmt->execute(['id' => $_POST['id']]);
        addLog($pdo, $userId, 'BANCO_REATIVADO', 'Banco ID ' . ($_POST['id']));
        header('Location: bancos.php');
        exit;
    }
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM bancos WHERE id = :id AND ativo = 1');
    $stmt->execute(['id' => $_GET['edit']]);
    $editingBanco = $stmt->fetch();
}

$bancos = $pdo->query('SELECT * FROM bancos WHERE ativo = 1 ORDER BY nome')->fetchAll();
$bancosInativos = $pdo->query('SELECT * FROM bancos WHERE ativo = 0 ORDER BY nome')->fetchAll();
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
                <h3><?= $editingBanco ? 'Editar banco' : 'Adicionar banco' ?></h3>
                <?php if ($editingBanco): ?>
                    <a class="button secondary" href="bancos.php">Cancelar edição</a>
                <?php endif; ?>
            </div>
            <form method="POST" class="form-grid">
                <input type="hidden" name="action" value="<?= $editingBanco ? 'update_banco' : 'add_banco' ?>">
                <?php if ($editingBanco): ?>
                    <input type="hidden" name="id" value="<?= $editingBanco['id'] ?>">
                <?php endif; ?>
                <div>
                    <label>Nome</label>
                    <input class="input" name="nome" required value="<?= htmlspecialchars($editingBanco['nome'] ?? '') ?>">
                </div>
                <div>
                    <label>Código do banco</label>
                    <input class="input" name="codigo_banco" required value="<?= htmlspecialchars($editingBanco['codigo_banco'] ?? '') ?>">
                </div>
                <div style="align-self:end;">
                    <button type="submit"><?= $editingBanco ? 'Atualizar' : 'Salvar' ?></button>
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
                            <a class="button secondary" href="bancos.php?edit=<?= $banco['id'] ?>">Editar</a>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Deseja desativar este banco da visualização?');">
                                <input type="hidden" name="action" value="delete_banco">
                                <input type="hidden" name="id" value="<?= $banco['id'] ?>">
                                <button class="danger" type="submit">Desativar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <?php if ($bancosInativos): ?>
        <section class="card">
            <div class="header">
                <h3>Bancos desativados</h3>
                <span class="muted">Visíveis apenas aqui</span>
            </div>
            <table class="table">
                <thead>
                    <tr><th>Nome</th><th>Código</th><th>Ações</th></tr>
                </thead>
                <tbody>
                <?php foreach ($bancosInativos as $banco): ?>
                    <tr>
                        <td><?= htmlspecialchars($banco['nome']) ?></td>
                        <td><?= htmlspecialchars($banco['codigo_banco']) ?></td>
                        <td>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Reativar este banco?');">
                                <input type="hidden" name="action" value="restore_banco">
                                <input type="hidden" name="id" value="<?= $banco['id'] ?>">
                                <button type="submit" class="button secondary">Reativar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <?php endif; ?>
    </main>
</div>
<?php if ($alertRestrict): ?>
<script>alert('Atenção: Não é possível excluir este banco pois existem cartões vinculados a ele (Regra ON DELETE RESTRICT).');</script>
<?php endif; ?>
</body>
</html>
