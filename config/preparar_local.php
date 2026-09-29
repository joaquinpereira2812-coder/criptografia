<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Acceso denegado.'); }
try {
    if (!extension_loaded('openssl')) throw new RuntimeException('Active la extension openssl en php.ini.');
    $archivo = __DIR__ . '/secrets.local.php';
    if (!is_file($archivo)) {
        $claves = ['APP_AES_KEY' => bin2hex(random_bytes(32)), 'APP_HMAC_KEY' => bin2hex(random_bytes(32))];
        $contenido = "<?php\n// Claves locales: conservar este archivo y excluirlo de Git.\nreturn " . var_export($claves, true) . ";\n";
        if (file_put_contents($archivo, $contenido, LOCK_EX) === false) throw new RuntimeException('No se pudieron guardar las claves locales.');
        chmod($archivo, 0600);
    }
    $privada = is_file(__DIR__ . '/privada.key');
    $publica = is_file(__DIR__ . '/publica.key');
    if ($privada !== $publica) throw new RuntimeException('Falta una de las dos claves RSA. Revise config antes de continuar.');
    if (!$privada) require __DIR__ . '/generar_rsa.php';
    require_once __DIR__ . '/crypto.php';
    $prueba = 'Telefono de prueba';
    if (descifrarAES256(cifrarAES256($prueba)) !== $prueba) throw new RuntimeException('Fallo en la prueba AES.');
    if (descifrarRSA(cifrarRSA($prueba)) !== $prueba) throw new RuntimeException('Fallo en la prueba RSA.');
    $hmac = claveEntorno('APP_HMAC_KEY');
    if (hash_equals(hash_hmac('sha256', 'CuentaA', $hmac), hash_hmac('sha256', 'CuentaB', $hmac))) throw new RuntimeException('Fallo en la prueba HMAC.');
    echo "Configuracion lista: pruebas AES, RSA y HMAC correctas.\nYa puede volver al formulario de registro.\n";
} catch (Throwable $ex) {
    fwrite(STDERR, 'Error: ' . $ex->getMessage() . "\n");
    exit(1);
}
