<?php
if(!defined('APP_DIR')) throw new Exception('App dir missing', 1);
use MiniRouter\Router;
// Opciones avanzadas del Router
$router=new Router('Cron');
\MiniRouter\classloader(APP_DIR.'/endpoints', '', '.php', $router->getMainNamespace(), true);
$router->prepareForCLI();
$router->loadEndPoint();
// Se encontró la ruta del endpoint
// Ya que se encontró la ruta. Aqui puede realizar validaciones de seguridad antes de ejecutar el endpoint
global $ROUTE;
$ROUTE=$router->getRoute();
unset($router);
// Se encontró la función que se ejecutará
$ROUTE->call();
