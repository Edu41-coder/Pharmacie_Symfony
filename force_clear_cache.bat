@echo off
echo ========================================
echo    NETTOYAGE FORCÉ DU CACHE SYMFONY    
echo ========================================
echo.

cd /d %~dp0

echo Tentative de suppression manuelle des répertoires de cache...
rmdir /S /Q var\cache\de_ 2>nul
rmdir /S /Q var\cache\dev 2>nul
rmdir /S /Q var\cache\prod 2>nul

echo Nettoyage du cache avec Symfony...
php bin/console cache:clear --no-warmup

echo Réinitialisation des droits sur le dossier var...
icacls var /reset /T /Q

echo Réchauffement du cache...
php bin/console cache:warmup

echo.
echo Cache nettoyé et réchauffé avec succès!
echo.
echo Appuyez sur une touche pour fermer cette fenêtre...
pause > nul
