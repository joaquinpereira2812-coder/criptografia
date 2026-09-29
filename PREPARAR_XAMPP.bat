@echo off
setlocal
cd /d "%~dp0"
set "PROJECT_PHP=%~dp0..\..\php\php.exe"
if not exist "%PROJECT_PHP%" set "PROJECT_PHP=C:\xampp\php\php.exe"
if not exist "%PROJECT_PHP%" (
  echo No se encontro PHP de XAMPP. Coloque login-base dentro de la carpeta htdocs de XAMPP.
  pause
  exit /b 1
)
"%PROJECT_PHP%" config\preparar_local.php
if errorlevel 1 (
  echo La configuracion no termino. Copie o fotografie el error que aparece arriba.
  pause
  exit /b 1
)
pause
