@echo off
echo ========================================
echo    VÉRIFICATION DES ROUTES SYMFONY     
echo ========================================
echo.

cd /d %~dp0

echo Liste de toutes les routes :
echo -----------------------------
php bin/console debug:router

echo.
echo Recherche des routes TVA :
echo -----------------------------
php bin/console debug:router | findstr tva

echo.
echo Appuyez sur une touche pour fermer cette fenêtre...
pause > nul
