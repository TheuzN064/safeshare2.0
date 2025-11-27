<?php
// Configuração simples de conexão (para fins didáticos)
$host = 'localhost';
$db   = 'vault_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Erro ao conectar no MySQL: ' . $e->getMessage());
}

// Para simplificar o exemplo deixamos o usuário fixo (id 1)
$userId = 1;

// Processamento de formulários (INSERT/DELETE) no mesmo arquivo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_login') {
        $sql = 'INSERT INTO logins (id_usuario, id_categoria, site_nome, site_url, login, senha)
                VALUES (:id_usuario, :id_categoria, :site_nome, :site_url, :login, :senha)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id_usuario'   => $userId,
            'id_categoria' => $_POST['id_categoria'] ?? null,
            'site_nome'    => $_POST['site_nome'] ?? '',
            'site_url'     => $_POST['site_url'] ?? null,
            'login'        => $_POST['login'] ?? '',
            'senha'        => $_POST['senha'] ?? '', // intencionalmente sem hash para visualização didática
        ]);
    }

    if ($action === 'add_card') {
        $sql = 'INSERT INTO cartoes (id_usuario, titular, numero, validade, cvv, bandeira)
                VALUES (:id_usuario, :titular, :numero, :validade, :cvv, :bandeira)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id_usuario' => $userId,
            'titular'    => $_POST['titular'] ?? '',
            'numero'     => $_POST['numero'] ?? '',
            'validade'   => $_POST['validade'] ?? '',
            'cvv'        => $_POST['cvv'] ?? '',
            'bandeira'   => $_POST['bandeira'] ?? '',
        ]);
    }

    if ($action === 'delete_login' && isset($_POST['id'])) {
        $stmt = $pdo->prepare('DELETE FROM logins WHERE id = :id AND id_usuario = :user');
        $stmt->execute(['id' => $_POST['id'], 'user' => $userId]);
    }

    if ($action === 'delete_card' && isset($_POST['id'])) {
        $stmt = $pdo->prepare('DELETE FROM cartoes WHERE id = :id AND id_usuario = :user');
        $stmt->execute(['id' => $_POST['id'], 'user' => $userId]);
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Buscar categorias para selects
$categorias = $pdo->query('SELECT id, nome, cor_hex FROM categorias ORDER BY nome')->fetchAll();

// Listagem de logins com INNER JOIN em categorias
$loginsStmt = $pdo->prepare('SELECT l.*, c.nome AS categoria_nome, c.cor_hex
                             FROM logins l
                             INNER JOIN categorias c ON l.id_categoria = c.id
                             WHERE l.id_usuario = :user
                             ORDER BY l.id DESC');
$loginsStmt->execute(['user' => $userId]);
$logins = $loginsStmt->fetchAll();

// Listagem de cartões
$cartoesStmt = $pdo->prepare('SELECT * FROM cartoes WHERE id_usuario = :user ORDER BY id DESC');
$cartoesStmt->execute(['user' => $userId]);
$cartoes = $cartoesStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vault | Gerenciador de Senhas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <h1>🔐 Vault</h1>
        <nav class="nav">
            <a href="#senhas">Senhas</a>
            <a href="#cartoes">Cartões</a>
            <a href="#novo" onclick="openModal('modal-login');return false;" class="btn primary" style="text-align:center;">+ Novo</a>
        </nav>
        <div>
            <p style="color:var(--muted); margin-bottom:6px;">Relacionamentos</p>
            <small>logins.id_categoria → categorias.id<br>logins.id_usuario → usuarios.id<br>cartoes.id_usuario → usuarios.id</small>
        </div>
    </aside>

    <main class="main">
        <section id="senhas" class="card">
            <div class="header">
                <h2>Senhas Salvas</h2>
                <div class="actions">
                    <button class="btn primary" onclick="openModal('modal-login')">+ Adicionar Senha</button>
                </div>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Site</th>
                            <th>Categoria</th>
                            <th>Login</th>
                            <th>Senha</th>
                            <th style="width:120px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($logins) === 0): ?>
                        <tr><td colspan="5">Nenhuma senha cadastrada.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logins as $login): ?>
                        <tr>
                            <td>
                                <div style="display:flex;flex-direction:column;gap:4px;">
                                    <strong><?= htmlspecialchars($login['site_nome']) ?></strong>
                                    <?php if (!empty($login['site_url'])): ?>
                                        <a href="<?= htmlspecialchars($login['site_url']) ?>" target="_blank" style="color:var(--muted); font-size:12px;">
                                            <?= htmlspecialchars($login['site_url']) ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge">
                                    <span class="dot" style="background: <?= htmlspecialchars($login['cor_hex']) ?>;"></span>
                                    <?= htmlspecialchars($login['categoria_nome']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($login['login']) ?></td>
                            <td>
                                <div class="password-field">
                                    <input type="password" value="<?= htmlspecialchars($login['senha']) ?>" readonly>
                                    <button class="icon-btn" title="Mostrar/ocultar" onclick="togglePassword(this)">👁️</button>
                                    <button class="icon-btn" title="Copiar" onclick="copyPassword(this)">📋</button>
                                </div>
                            </td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_login">
                                    <input type="hidden" name="id" value="<?= $login['id'] ?>">
                                    <button type="submit" class="icon-btn danger" onclick="return confirm('Excluir este login?')">✖</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="cartoes" class="card">
            <div class="header">
                <h2>Cartões</h2>
                <div class="actions">
                    <button class="btn" onclick="openModal('modal-cartao')">+ Adicionar Cartão</button>
                </div>
            </div>
            <div class="grid-cards">
                <?php if (count($cartoes) === 0): ?>
                    <p style="color:var(--muted);">Nenhum cartão cadastrado.</p>
                <?php else: ?>
                    <?php foreach ($cartoes as $cartao): ?>
                        <div class="credit-card">
                            <div class="brand">
                                <span><?= htmlspecialchars($cartao['bandeira']) ?></span>
                                <form method="post">
                                    <input type="hidden" name="action" value="delete_card">
                                    <input type="hidden" name="id" value="<?= $cartao['id'] ?>">
                                    <button type="submit" class="icon-btn danger" style="background:rgba(0,0,0,0.2);" onclick="return confirm('Excluir este cartão?')">✖</button>
                                </form>
                            </div>
                            <div class="number"><?= htmlspecialchars(chunk_split($cartao['numero'], 4, ' ')) ?></div>
                            <div class="meta">
                                <div>
                                    <div style="font-size:11px; opacity:0.8;">TITULAR</div>
                                    <strong><?= htmlspecialchars($cartao['titular']) ?></strong>
                                </div>
                                <div>
                                    <div style="font-size:11px; opacity:0.8;">VALIDADE</div>
                                    <strong><?= htmlspecialchars($cartao['validade']) ?></strong>
                                </div>
                                <div>
                                    <div style="font-size:11px; opacity:0.8;">CVV</div>
                                    <strong><?= htmlspecialchars($cartao['cvv']) ?></strong>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>

<!-- Modal: Novo Login -->
<div class="modal-backdrop" id="modal-login">
    <div class="modal">
        <header>
            <h3>Nova Senha</h3>
            <button class="icon-btn" onclick="closeModal('modal-login')">✖</button>
        </header>
        <form method="post">
            <input type="hidden" name="action" value="add_login">
            <div class="form-grid">
                <div>
                    <label>Nome do site</label>
                    <input type="text" name="site_nome" required>
                </div>
                <div>
                    <label>URL (opcional)</label>
                    <input type="url" name="site_url" placeholder="https://...">
                </div>
                <div>
                    <label>Categoria</label>
                    <select name="id_categoria" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Login</label>
                    <input type="text" name="login" required>
                </div>
                <div>
                    <label>Senha (texto puro para visualização)</label>
                    <input type="text" name="senha" required>
                </div>
            </div>
            <footer>
                <button type="button" class="btn" onclick="closeModal('modal-login')">Cancelar</button>
                <button type="submit" class="btn primary">Salvar</button>
            </footer>
        </form>
    </div>
</div>

<!-- Modal: Novo Cartão -->
<div class="modal-backdrop" id="modal-cartao">
    <div class="modal">
        <header>
            <h3>Novo Cartão</h3>
            <button class="icon-btn" onclick="closeModal('modal-cartao')">✖</button>
        </header>
        <form method="post">
            <input type="hidden" name="action" value="add_card">
            <div class="form-grid">
                <div>
                    <label>Titular</label>
                    <input type="text" name="titular" required>
                </div>
                <div>
                    <label>Número</label>
                    <input type="text" name="numero" required>
                </div>
                <div>
                    <label>Validade (MM/AAAA)</label>
                    <input type="text" name="validade" placeholder="05/2030" required>
                </div>
                <div>
                    <label>CVV</label>
                    <input type="text" name="cvv" required>
                </div>
                <div>
                    <label>Bandeira</label>
                    <input type="text" name="bandeira" placeholder="Visa, Master..." required>
                </div>
            </div>
            <footer>
                <button type="button" class="btn" onclick="closeModal('modal-cartao')">Cancelar</button>
                <button type="submit" class="btn primary">Salvar</button>
            </footer>
        </form>
    </div>
</div>

<script>
function togglePassword(btn) {
    const input = btn.previousElementSibling;
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
}

function copyPassword(btn) {
    const input = btn.previousElementSibling.previousElementSibling;
    if (!input) return;
    navigator.clipboard.writeText(input.value).then(() => {
        btn.textContent = '✔';
        setTimeout(() => btn.textContent = '📋', 1200);
    });
}

function openModal(id) {
    document.getElementById(id)?.classList.add('active');
}

function closeModal(id) {
    document.getElementById(id)?.classList.remove('active');
}

// Fechar modal clicando fora do conteúdo
['modal-login', 'modal-cartao'].forEach(id => {
    const backdrop = document.getElementById(id);
    backdrop?.addEventListener('click', (e) => {
        if (e.target === backdrop) closeModal(id);
    });
});
</script>
</body>
</html>
