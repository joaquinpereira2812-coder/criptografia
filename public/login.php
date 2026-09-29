<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../config/database.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $stmt = $pdo->prepare('SELECT id, username, password FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    // password_verify contrasta la entrada con el hash y su sal incorporada.
    if ($user && password_verify($password, $user['password'])) {
        try {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['login_time'] = date('Y-m-d H:i:s');
            header('Location: dashboard.php'); exit;
        } catch (RuntimeException $ex) { error_log($ex->getMessage()); $error = 'No se pudo iniciar la sesión segura. Revisá las claves RSA.'; }
    } else $error = 'Usuario o contraseña incorrectos.';
}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Ingresar</title><link rel="stylesheet" href="style.css"></head><body><main class="card narrow"><h1>Ingresar</h1><?php if ($error): ?><p class="alert error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?><form method="post"><label>Usuario<input name="username" required autocomplete="username"></label><label>Contraseña<input name="password" type="password" required autocomplete="current-password"></label><button>Iniciar sesión</button></form><p class="link">¿No tenés cuenta? <a href="register.php">Registrarse</a></p></main></body></html>
