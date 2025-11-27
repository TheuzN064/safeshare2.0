<?php
require_once 'db.php';
requireLogin($pdo);
$currentPage = 'dashboard';

// Estatísticas gerais
$stmt = $pdo->prepare('SELECT COUNT(*) FROM logins WHERE id_usuario = :user AND ativo = 1');
$stmt->execute(['user' => $userId]);
$totalSenhas = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM cartoes WHERE id_usuario = :user AND ativo = 1');
$stmt->execute(['user' => $userId]);
$totalCartoes = (int) $stmt->fetchColumn();

$totalBancos = (int) $pdo->query('SELECT COUNT(*) FROM bancos WHERE ativo = 1')->fetchColumn();
$totalLogs = (int) $pdo->query('SELECT COUNT(*) FROM logs')->fetchColumn();

$categoriasResumo = $pdo->query('SELECT c.nome, c.cor_hex, COUNT(l.id) AS total FROM categorias c LEFT JOIN logins l ON l.id_categoria = c.id AND l.ativo = 1 GROUP BY c.id, c.nome, c.cor_hex')->fetchAll();
$bancosResumo = $pdo->query('SELECT b.nome, COUNT(c.id) AS total FROM bancos b LEFT JOIN cartoes c ON c.id_banco = b.id AND c.ativo = 1 WHERE b.ativo = 1 GROUP BY b.id, b.nome')->fetchAll();
$logsRecentes = $pdo->query('SELECT l.acao, l.detalhes, l.criado_em, u.nome AS usuario_nome FROM logs l LEFT JOIN usuarios u ON l.id_usuario = u.id ORDER BY l.id DESC LIMIT 5')->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vault | Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">
    <?php include 'navbar.php'; ?>
    <main class="main">
        <div class="header">
            <div>
                <p class="muted">Visão consolidada</p>
                <h2>Dashboard</h2>
            </div>
            <div class="flex">
                <a class="button secondary" href="bancos.php">Gerenciar Bancos</a>
                <a class="button" href="cartoes.php">Adicionar Cartão</a>
            </div>
        </div>

        <section class="grid-3">
            <div class="stat">
                <p>Senhas</p>
                <strong><?= $totalSenhas ?></strong>
                <small>logins.id_usuario → usuarios.id</small>
            </div>
            <div class="stat">
                <p>Cartões</p>
                <strong><?= $totalCartoes ?></strong>
                <small>cartoes.id_usuario → usuarios.id</small>
            </div>
            <div class="stat">
                <p>Bancos</p>
                <strong><?= $totalBancos ?></strong>
                <small>cartoes.id_banco → bancos.id (ON DELETE RESTRICT)</small>
            </div>
            <div class="stat">
                <p>Logs</p>
                <strong><?= $totalLogs ?></strong>
                <small>Auditoria de ações (logs.id_usuario)</small>
            </div>
        </section>

        <section class="card">
            <div class="header">
                <h2>Logins por categoria</h2>
                <a class="button secondary" href="senhas.php">Ir para Senhas</a>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Categoria</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($categoriasResumo as $cat): ?>
                    <tr>
                        <td>
                            <span class="badge"><span class="dot" style="background: <?= htmlspecialchars($cat['cor_hex']) ?>"></span><?= htmlspecialchars($cat['nome']) ?></span>
                        </td>
                        <td><?= (int) $cat['total'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section class="card">
            <div class="header">
                <h2>Cartões por banco</h2>
                <a class="button secondary" href="cartoes.php">Ir para Cartões</a>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Banco</th>
                        <th>Total de Cartões</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($bancosResumo as $banco): ?>
                    <tr>
                        <td><?= htmlspecialchars($banco['nome']) ?></td>
                        <td><?= (int) $banco['total'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section class="card">
            <div class="header">
                <h2>Últimos logs</h2>
                <a class="button secondary" href="logs.php">Ver todos</a>
            </div>
            <table class="table log-table">
                <thead>
                    <tr><th>Quando</th><th>Ação</th><th>Detalhes</th><th>Usuário</th></tr>
                </thead>
                <tbody>
                <?php foreach ($logsRecentes as $log): ?>
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
