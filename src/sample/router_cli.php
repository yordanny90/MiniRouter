<?php

use MiniRouter\Request;
use MiniRouter\Response;
use MiniRouter\RouteException;
use MiniRouter\Router;
use function MiniRouter\classloader;

if(!defined('APP_DIR')) throw new Exception('App dir missing', 1);
Response::clearBuffer(true);
if(!Request::isCLI()){
	Response::r_forbidden()->send();
	exit;
}
try{
	$router=Router::startCli('AppTask');
    $router->bindResourceDir(APP_DIR.'/Routes');
	classloader(APP_DIR.'/Routes', '', '.php', $router->getMainNamespace(), true);
	$router->prepare();
	global $ROUTE;
	$ROUTE=$router->getRoute();
	unset($router);
	// Se encontró la función que se ejecutará
	// Aqui pueden realizar validaciones de seguridad antes de ejecutar el endpoint
	// Conexiones a bases de datos, u otros servicios externos que puedan retrazar la ejecución
	$ROUTE->call();
}catch(RouteException $e){
	$e->getResponse()->send();
}
