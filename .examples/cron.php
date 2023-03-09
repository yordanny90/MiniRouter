<?php

use MiniRouter\Response;
use MiniRouter\RouterP;

// Habilitarlo para el ambiente de producción
//error_reporting(0);

define('BASE_DIR', realpath(__DIR__.'/..'));
// Se carga la librería del MiniRouter
require_once __DIR__.'/init.php';
Response::flushBuffer();
// Opciones avanzadas del Router
$router=new RouterP('Cron');
\MiniRouter\classloader(APP_DIR.'/endpoints', '', '.php', $router->getMainNamespace(), true);
//$router->missing_class='';
//$router->max_subdir=1;
//$router->received_path=\MiniRouter\RequestCLI::getArgText(0);
//Router::$received_method='CLI';
$router->prepareForCLI();
// Se encontró la ruta del endpoint
// Ya que se encontró la ruta. Aqui puede realizar validaciones de seguridad antes de ejecutar el endpoint
global $ROUTE;
$ROUTE=$router->getRoute();
unset($router);
// Se encontró la función que se ejecutará
$ROUTE->call();
