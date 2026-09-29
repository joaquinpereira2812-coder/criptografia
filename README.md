# Sistema de login con criptografía aplicada

Proyecto del Práctico 2 (partes 1, 2 y 3). PHP nativo, PDO/MySQL, Bcrypt, AES-256-CBC, RSA-2048 y HMAC-SHA256.

## Requisitos

- PHP 8.0 o superior con extensiones `pdo_mysql` y `openssl`.
- MySQL o MariaDB y un servidor web (por ejemplo XAMPP).
- Configurar el document root para apuntar a `public/`, o instalar la carpeta `login-base` bajo `htdocs` y visitar `/login-base/public/`. No publicar `config/` como ruta web en un servidor real.

## Instalación

1. Copiar `login-base` a `htdocs` de XAMPP.
2. Ejecutar `schema.sql` en phpMyAdmin. Se crea `login_system` y la tabla `users`.
3. Configurar `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` según MySQL (por defecto localhost/login_system/root/sin contraseña, solo entorno local).
4. Dentro de `login-base`, hacer doble clic en `PREPARAR_XAMPP.bat`. Genera claves AES/HMAC locales y el par RSA, y verifica los tres algoritmos. Esperar el mensaje `Configuracion lista`. Si se usa otro sistema, ejecutar `php config/preparar_local.php`.
5. Conservar `config/secrets.local.php` y `config/privada.key`: contienen secretos y quedan excluidos de Git. El instalador conserva las claves existentes cuando se vuelve a ejecutar. Las variables de entorno `APP_AES_KEY` y `APP_HMAC_KEY`, si se configuraron, tienen prioridad sobre las claves locales.
6. Abrir `http://localhost/login-base/public/register.php` y crear un usuario con teléfono. Luego iniciar sesión en `login.php`.

## Funcionamiento

- `public/register.php`: valida campos, guarda contraseña con `password_hash(..., PASSWORD_BCRYPT)` y cifra el teléfono con IV aleatorio AES-256-CBC. El dato cifrado incluye un HMAC para detectar modificaciones antes de descifrar.
- `public/login.php`: busca por usuario y usa `password_verify`; emite un token aleatorio cifrado con la clave pública RSA y comprueba su descifrado con la clave privada. La sesión PHP sigue siendo el mecanismo de autenticación; el token RSA es una demostración del práctico.
- `public/dashboard.php`: exige sesión, muestra datos de usuario, teléfono descifrado y ejemplo del token RSA. Salir destruye la sesión.
- `public/validar_integridad.php`: exige sesión y compara la firma HMAC-SHA256 de un mensaje original y otro alterado mediante `hash_equals`.
- `config/database.php`: conexión PDO con consultas preparadas y errores genéricos de cara al usuario.
- `config/crypto.php`: funciones AES y RSA. `config/generar_rsa.php`: generación local del par RSA.
- `schema.sql`: base y tabla completas; el campo `salt` se incluye porque aparece en la guía, aunque Bcrypt almacena su propia sal en el hash y no utiliza esa columna.

## Comprobaciones manuales

1. Sin sesión, abrir `dashboard.php` y `validar_integridad.php`: deben redirigir al login.
2. Registrar un usuario con teléfono: inspeccionar en la BD que `password` no sea el texto ingresado y `telefono_cifrado` sea Base64. Dos registros con idéntico teléfono deben producir valores distintos.
3. Probar contraseña incorrecta y correcta; en el panel verificar teléfono legible y los fragmentos AES/RSA.
4. Abrir HMAC: primera prueba válida, segunda inválida.
5. Salir y probar nuevamente el panel: debe redirigir al login.

## Seguridad y límites del ejercicio

No subir claves privadas, variables de entorno ni capturas que muestren tokens a un repositorio público. El token descifrado se exhibe por exigencia didáctica y debe eliminarse de una aplicación real. AES-CBC se autentica aquí con HMAC; en un sistema nuevo se suele preferir un modo AEAD como AES-GCM. Añadir CSRF, límites de intentos, HTTPS, cookies seguras y gestión formal de secretos antes de usar el sistema en producción.

## Entrega

El PDF de respuestas acompaña este proyecto. Las capturas de cada fase y la URL del repositorio requieren ejecutar el programa y publicar desde la cuenta del estudiante; no se incluyen como pruebas simuladas.
