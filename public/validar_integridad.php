<?php
declare(strict_types=1);
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
require_once __DIR__ . '/../config/crypto.php';
try { $clave = claveEntorno('APP_HMAC_KEY'); }
catch (RuntimeException $ex) { http_response_code(500); exit('Falta configurar APP_HMAC_KEY.'); }
/** El emisor firma el payload con una clave secreta; el receptor recalcula y compara con hash_equals. */
$original = 'user_id=1&monto=5000&destino=CuentaA';
$modificado = 'user_id=1&monto=5000&destino=CuentaB';
$firma = hash_hmac('sha256', $original, $clave);
$firmaModificada = hash_hmac('sha256', $modificado, $clave);
$valido = hash_equals($firma, hash_hmac('sha256', $original, $clave));
$alterado = hash_equals($firma, hash_hmac('sha256', $modificado, $clave));
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Integridad HMAC</title><link rel="stylesheet" href="style.css"></head><body><main class="card wide"><h1>Verificación HMAC-SHA256</h1><section><h2>Payload original</h2><code><?= e($original) ?></code><p>Firma: <code><?= e($firma) ?></code></p><p class="alert <?= $valido ? 'success' : 'error' ?>"><?= $valido ? 'Firma válida: el contenido coincide.' : 'Firma inválida.' ?></p></section><section><h2>Payload modificado</h2><code><?= e($modificado) ?></code><p>Firma recalculada: <code><?= e($firmaModificada) ?></code></p><p class="alert <?= $alterado ? 'success' : 'error' ?>"><?= $alterado ? 'Firma válida.' : 'Firma inválida: se detectó la alteración.' ?></p></section><p>La clave secreta participa en la firma; <code>hash_equals()</code> compara las firmas sin revelar diferencias de tiempo por el prefijo coincidente.</p><p class="link"><a href="dashboard.php">Volver al panel</a></p></main></body></html>
