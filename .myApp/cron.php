<?php

use MiniRouter\Response;
use MiniRouter\Router;

// Habilitarlo para el ambiente de producción
//error_reporting(0);

define('BASE_DIR', __DIR__.'/..');
// Se carga la librería del MiniRouter
require_once __DIR__.'/init.php';
Response::flushBuffer();
// Opciones avanzadas del Router
//Router::$endpoint_file_prefix='';
//Router::$endpoint_file_suffix='.php';
//Router::$default_path='index';
//Router::$missing_class='';
//Router::$max_subdir=1;
//Router::$received_path=null;
//Router::$received_method='CLI';
$router=new Router('Cron');
\MiniRouter\classloader(APP_DIR.'/endpoints', '', '.php', $router->getMainNamespace());
$router->prepareForCLI();
$router->loadEndPoint();
// Se encontró la ruta del endpoint
// Ya que se encontró la ruta. Aqui puede realizar validaciones de seguridad antes de ejecutar el endpoint
global $ROUTE;
$ROUTE=$router->getRoute();
unset($router);
// Se encontró la función que se ejecutará
$ROUTE->call();
