<?php
require_once 'db.php';
requireLogin($pdo);
$currentPage = 'logs';

$sql = 'SELECT l.*, u.nome AS usuario_nome FROM logs l LEFT JOIN usuarios u ON l.id_usuario = u.id ORDER BY l.id DESC LIMIT 200';
$logs = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vault | Logs</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">
    <?php include 'navbar.php'; ?>
    <main class="main">
        <div class="header">
            <div>
                <p class="muted">Auditoria simples</p>
                <h2>Logs</h2>
            </div>
            <a class="button secondary" href="index.php">Voltar ao Dashboard</a>
        </div>

        <section class="card">
            <div class="header">
                <h3>Eventos recentes</h3>
                <span class="tag">logs.id_usuario → usuarios.id</span>
            </div>
            <table class="table log-table">
                <thead>
                    <tr>
                        <th>Data/Hora</th>
                        <th>Ação</th>
                        <th>Detalhes</th>
                        <th>Usuário</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><span class="muted"><?= htmlspecialchars($log['criado_em']) ?></span></td>
                        <td><span class="pill"><?= htmlspecialchars($log['acao']) ?></span></td>
                        <td><?= htmlspecialchars($log['detalhes']) ?></td>
                        <td><?= htmlspecialchars($log['usuario_nome'] ?? 'N/D') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>
</body>
</html>
