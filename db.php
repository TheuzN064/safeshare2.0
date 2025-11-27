<?php
// Conexão PDO centralizada
session_start();

$host = 'localhost';
$db   = 'vault_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host={$host};dbname={$db};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Erro ao conectar no MySQL: ' . $e->getMessage());
}

function getCurrentUser(PDO $pdo): ?array {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT id, nome, email FROM usuarios WHERE id = :id');
    $stmt->execute(['id' => $_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function requireLogin(PDO $pdo): void {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

$currentUser = getCurrentUser($pdo);
$userId = $currentUser['id'] ?? null;
?>
