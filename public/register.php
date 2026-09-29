<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../config/database.php';
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');
    $telefono = trim((string)($_POST['telefono'] ?? ''));
    if ($username === '' || $email === '' || $password === '' || $confirm === '') $error = 'Completá los campos obligatorios.';
    elseif (strlen($username) > 50 || strlen($email) > 100 || strlen($telefono) > 40) $error = 'Algún campo supera el largo permitido.';
    elseif ($password !== $confirm) $error = 'Las contraseñas no coinciden.';
    elseif (strlen($password) < 6) $error = 'La contraseña debe tener al menos 6 caracteres.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'El correo electrónico no es válido.';
    else {
        try {
            $stmt = $pdo->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
            // password_hash genera una sal única automáticamente y guarda el hash irreversible.
            $stmt->execute([$username, $email, password_hash($password, PASSWORD_BCRYPT)]);
            $success = 'Registro exitoso. Ya podés ingresar.';
        } catch (PDOException $ex) {
            $error = $ex->getCode() === '23000' ? 'El usuario o correo ya existe.' : 'No se pudo completar el registro.';
        } catch (RuntimeException $ex) { $error = 'Falta configurar el cifrado del servidor.'; }
    }
}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Registro</title><link rel="stylesheet" href="style.css"></head><body>
<main class="card narrow"><h1>Crear cuenta</h1>
<?php if ($error): ?><p class="alert error"><?= e($error) ?></p><?php endif; ?>
<?php if ($success): ?><p class="alert success"><?= e($success) ?></p><?php endif; ?>
<form method="post"><label>Usuario<input name="username" maxlength="50" required value="<?= e($username ?? '') ?>"></label><label>Correo electrónico<input name="email" type="email" maxlength="100" required value="<?= e($email ?? '') ?>"></label><label>Contraseña<input name="password" type="password" minlength="6" required></label><label>Confirmar contraseña<input name="confirm_password" type="password" minlength="6" required></label><button>Registrarse</button></form><p class="link">¿Ya tenés cuenta? <a href="login.php">Ingresar</a></p></main></body></html>
