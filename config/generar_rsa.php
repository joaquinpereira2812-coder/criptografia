<?php
declare(strict_types=1);
// Ejecutar únicamente desde CLI: php config/generar_rsa.php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Acceso denegado.'); }
$rutaPrivada = __DIR__ . '/privada.key';
$rutaPublica = __DIR__ . '/publica.key';
if (file_exists($rutaPrivada) || file_exists($rutaPublica)) {
    exit("Las claves ya existen; no se sobrescribieron.\n");
}
$par = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
if ($par === false || !openssl_pkey_export($par, $privada)) throw new RuntimeException('No se pudo generar RSA.');
$publica = openssl_pkey_get_details($par)['key'];
if (file_put_contents($rutaPrivada, $privada, LOCK_EX) === false) throw new RuntimeException('No se pudo guardar la clave privada.');
chmod($rutaPrivada, 0600);
if (file_put_contents($rutaPublica, $publica, LOCK_EX) === false) throw new RuntimeException('No se pudo guardar la clave pública.');
echo "Claves RSA-2048 generadas.\n";
