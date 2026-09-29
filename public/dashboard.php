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
require_once __DIR__ . '/../config/crypto.php';
$stmt = $pdo->prepare('SELECT username, email, telefono_cifrado FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if (!$user) { $_SESSION = []; session_destroy(); header('Location: login.php'); exit; }
try { $telefono = $user['telefono_cifrado'] ? descifrarAES256($user['telefono_cifrado']) : 'No registrado'; }
catch (RuntimeException $ex) { $telefono = 'No se pudo verificar el dato cifrado'; }
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Panel de usuario</title><link rel="stylesheet" href="style.css"></head><body><main class="card wide"><header><h1>Panel de usuario</h1><form method="post"><button class="danger" name="logout" value="1">Salir</button></form></header><p>Bienvenido, <strong><?= e($user['username']) ?></strong>.</p>
<section><h2>Sesión</h2><p>Usuario ID: <?= (int)$_SESSION['user_id'] ?><br>Inicio: <?= e((string)$_SESSION['login_time']) ?></p></section>
<section><h2>Datos cifrados con AES-256-CBC</h2><p>Teléfono en BD: <code><?= e($user['telefono_cifrado'] ? substr($user['telefono_cifrado'], 0, 30) . '…' : 'N/A') ?></code></p><p>Teléfono descifrado: <strong><?= e($telefono) ?></strong></p></section>
<section><h2>Token cifrado con RSA-2048</h2><p>Token cifrado: <code><?= e(substr((string)($_SESSION['token_rsa_cifrado'] ?? ''), 0, 30)) ?>…</code></p><p>Token descifrado (demostración): <code><?= e((string)($_SESSION['token_rsa_descifrado'] ?? 'N/A')) ?></code></p></section>
<p class="link"><a href="validar_integridad.php">Probar validación HMAC-SHA256</a></p></main></body></html>
