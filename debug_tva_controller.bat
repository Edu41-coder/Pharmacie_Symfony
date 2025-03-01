
@echo off
echo ========================================
echo     DIAGNOSTIC AVANCÉ DU TVACONTROLLER  
echo ========================================
echo.

cd /d %~dp0

echo Vérification des classes Symfony...
echo.

echo 1. Contenu du TvaController:
type src\Controller\TvaController.php
echo.

echo 2. Vérification avec debug:controller:
php bin/console debug:controller App\\Controller\\TvaController 2>&1
echo.

echo 3. Liste de tous les contrôleurs dans le système:
php bin/console debug:container --tag=controller.service_arguments 2>&1
echo.

echo 4. Vérification des erreurs dans le TvaController:
php -l src\Controller\TvaController.php
echo.

echo 5. Création d'un TvaController de test:
echo.
echo ^<?php > src\Controller\TvaTestController.php
echo. >> src\Controller\TvaTestController.php
echo namespace App\Controller; >> src\Controller\TvaTestController.php
echo. >> src\Controller\TvaTestController.php
echo use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; >> src\Controller\TvaTestController.php
echo use Symfony\Component\HttpFoundation\Response; >> src\Controller\TvaTestController.php
echo use Symfony\Component\Routing\Annotation\Route; >> src\Controller\TvaTestController.php
echo. >> src\Controller\TvaTestController.php
echo /** >> src\Controller\TvaTestController.php
echo  * @Route("/tva-test") >> src\Controller\TvaTestController.php
echo  */ >> src\Controller\TvaTestController.php
echo class TvaTestController extends AbstractController >> src\Controller\TvaTestController.php
echo { >> src\Controller\TvaTestController.php
echo     /** >> src\Controller\TvaTestController.php
echo      * @Route("/", name="tva_test") >> src\Controller\TvaTestController.php
echo      */ >> src\Controller\TvaTestController.php
echo     public function index(): Response >> src\Controller\TvaTestController.php
echo     { >> src\Controller\TvaTestController.php
echo         return new Response('Test TVA Controller works!'); >> src\Controller\TvaTestController.php
echo     } >> src\Controller\TvaTestController.php
echo } >> src\Controller\TvaTestController.php

echo 6. Suppression du cache:
rmdir /S /Q var\cache\dev 2>nul
mkdir var\cache\dev 2>nul
php bin/console cache:clear
echo.

echo 7. Vérification des routes (y compris la nouvelle route de test):
php bin/console debug:router | findstr tva
echo.

echo Si la route "tva_test" apparaît mais pas les autres routes TVA,
echo alors le problème pourrait être lié à l'utilisation des annotations dans TvaController.
echo.

echo Appuyez sur une touche pour fermer cette fenêtre...
pause > nul
