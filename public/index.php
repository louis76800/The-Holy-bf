<?php
use src\{Autoloader, Router};

// On démarre la session
if (session_status() === PHP_SESSION_NONE){
    session_start();
    //var_dump($_SESSION);
}

// CONSTANTES : Racine du projet
define('ROOT', dirname(__DIR__));
define('SITE_TITLE', 'The Holy BF');
define('DS', DIRECTORY_SEPARATOR);

// AUTOLOAD
// Librairies externes composer autoload (twig...)
require_once(ROOT."/vendor/autoload.php");
// Autoloader interne à l'application
require_once(ROOT . "/src/Autoloader.php");
Autoloader::register();

// ROUTER / DISPATCHER
$app = new Router();
$app->start();