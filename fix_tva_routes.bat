@echo off
echo ========================================
echo     CORRECTION DES ROUTES TVA           
echo ========================================
echo.

cd /d %~dp0

echo Étape 1: Mise à jour de l'autoloader Composer...
composer dump-autoload -o

echo.
echo Étape 2: Suppression du cache...
rmdir /S /Q var\cache\de_ 2>nul
rmdir /S /Q var\cache\dev 2>nul
rmdir /S /Q var\cache\prod 2>nul
mkdir var\cache\dev 2>nul

echo.
echo Étape 3: Nettoyage du cache avec Symfony...
php bin/console cache:clear --no-warmup

echo.
echo Étape 4: Réchauffement du cache...
php bin/console cache:warmup

echo.
echo Étape 5: Vérification des routes...
php bin/console debug:router | findstr tva

echo.
if %ERRORLEVEL% NEQ 0 (
    echo [AVERTISSEMENT] Les routes TVA ne sont toujours pas détectées.
    echo Tentative de diagnostic supplémentaire...
    
    echo.
    echo Étape 6: Diagnostic du contrôleur TvaController...
    
    if not exist src\Controller\TvaController.php (
        echo [ERREUR] Le contrôleur TvaController.php est manquant!
        goto end
    )
    
    echo [INFO] Veuillez vérifier que l'annotation Route est correctement importée:
    echo use Symfony\Component\Routing\Annotation\Route;
    echo.
    echo [INFO] Veuillez vous assurer que les annotations de route sont correctement formatées:
    echo  /**
    echo   * @Route^("/", name="tva_show", methods={"GET"}^)
    echo   */
    
) else (
    echo [SUCCÈS] Les routes TVA sont maintenant détectées!
)

:end
echo.
echo Appuyez sur une touche pour fermer cette fenêtre...
pause > nul
