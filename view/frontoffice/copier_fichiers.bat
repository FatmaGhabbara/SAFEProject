@echo off
echo ========================================
echo   Copie des fichiers vers htdocs
echo ========================================
echo.

set SOURCE=%~dp0
set DEST=C:\xampp\htdocs\SAFEProject\SAFEProject\view\frontoffice

echo Source: %SOURCE%
echo Destination: %DEST%
echo.

if not exist "%DEST%" (
    echo Creation du dossier de destination...
    mkdir "%DEST%"
)

echo Copie des fichiers...
copy /Y "%SOURCE%ajouter_signalement.php" "%DEST%\"
copy /Y "%SOURCE%detail_signalement.php" "%DEST%\"
copy /Y "%SOURCE%supprimer_signalement.php" "%DEST%\"
copy /Y "%SOURCE%mes_signalements.php" "%DEST%\"
copy /Y "%SOURCE%api.php" "%DEST%\"

echo.
echo ========================================
echo   Copie terminee !
echo ========================================
echo.
echo Fichiers copies :
echo   - ajouter_signalement.php
echo   - detail_signalement.php
echo   - supprimer_signalement.php
echo   - mes_signalements.php
echo   - api.php
echo.
echo Testez maintenant dans votre navigateur !
echo.
pause

