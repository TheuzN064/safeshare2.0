<?php
// navbar reutilizável
$currentPage = $currentPage ?? '';
?>
<aside class="sidebar">
    <h1>🔐 Vault</h1>
    <nav class="nav">
        <a class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>" href="index.php">Dashboard</a>
        <a class="<?= $currentPage === 'bancos' ? 'active' : '' ?>" href="bancos.php">Bancos</a>
        <a class="<?= $currentPage === 'cartoes' ? 'active' : '' ?>" href="cartoes.php">Cartões</a>
        <a class="<?= $currentPage === 'senhas' ? 'active' : '' ?>" href="senhas.php">Senhas</a>
    </nav>
    <div class="relationships">
        <p>Relacionamentos</p>
        <small>
            logins.id_usuario → usuarios.id<br>
            logins.id_categoria → categorias.id<br>
            cartoes.id_usuario → usuarios.id<br>
            cartoes.id_banco → bancos.id (ON DELETE RESTRICT)
        </small>
    </div>
</aside>
