<?php
declare(strict_types=1);

/** AES-256-CBC: IV aleatorio y HMAC autenticador sobre IV+cifrado. */
function claveEntorno(string $nombre): string {
    $valor = getenv($nombre);
    if ($valor === false || $valor === '') {
        $archivo = __DIR__ . '/secrets.local.php';
        $local = is_file($archivo) ? require $archivo : [];
        $valor = is_array($local) ? ($local[$nombre] ?? false) : false;
    }
    if ($valor === false || strlen($valor) < 32) {
        throw new RuntimeException("Configure {$nombre} con al menos 32 caracteres.");
    }
    return $valor;
}

function cifrarAES256(string $textoPlano): string {
    $maestra = claveEntorno('APP_AES_KEY');
    $clave = hash('sha256', 'aes:' . $maestra, true);
    $macKey = hash('sha256', 'mac:' . $maestra, true);
    $iv = random_bytes(16);
    $cifrado = openssl_encrypt($textoPlano, 'aes-256-cbc', $clave, OPENSSL_RAW_DATA, $iv);
    if ($cifrado === false) throw new RuntimeException('Error de cifrado AES.');
    $datos = $iv . $cifrado;
    return base64_encode($datos . hash_hmac('sha256', $datos, $macKey, true));
}

function descifrarAES256(string $cadenaBase64): string {
    $binario = base64_decode($cadenaBase64, true);
    if ($binario === false || strlen($binario) < 64) throw new RuntimeException('Dato cifrado inválido.');
    $maestra = claveEntorno('APP_AES_KEY');
    $datos = substr($binario, 0, -32);
    $mac = substr($binario, -32);
    $esperado = hash_hmac('sha256', $datos, hash('sha256', 'mac:' . $maestra, true), true);
    if (!hash_equals($esperado, $mac)) throw new RuntimeException('Integridad del dato cifrado inválida.');
    $texto = openssl_decrypt(substr($datos, 16), 'aes-256-cbc', hash('sha256', 'aes:' . $maestra, true), OPENSSL_RAW_DATA, substr($datos, 0, 16));
    if ($texto === false) throw new RuntimeException('Error de descifrado AES.');
    return $texto;
}

