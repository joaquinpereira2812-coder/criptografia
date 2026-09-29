<?php
declare(strict_types=1);
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy(); header('Location: login.php'); exit;
}
require_once __DIR__ . '/../config/database.php';
$stmt = $pdo->prepare('SELECT username, email, telefono_cifrado FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if (!$user) { $_SESSION = []; session_destroy(); header('Location: login.php'); exit; }
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Panel de usuario</title><link rel="stylesheet" href="style.css"></head><body><main class="card wide"><header><h1>Panel de usuario</h1><form method="post"><button class="danger" name="logout" value="1">Salir</button></form></header><p>Bienvenido, <strong><?= e($user['username']) ?></strong>.</p>
<section><h2>Sesión</h2><p>Usuario ID: <?= (int)$_SESSION['user_id'] ?><br>Inicio: <?= e((string)$_SESSION['login_time']) ?></p></section>
</main></body></html>
