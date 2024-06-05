<?php

use MiniRouter\Request;
use MiniRouter\Response;
use MiniRouter\RouteException;
use MiniRouter\Router;
use function MiniRouter\classloader;

if(!defined('APP_DIR')) throw new Exception('App dir missing', 1);
Response::clearBuffer(true);
if(Request::isCLI()){
	Response::r_forbidden()->content('Execution by CLI is not allowed')->send();
	exit;
}
try{
	Response::addHeaders([
		'Access-Control-Allow-Origin'=>'*',
		'Access-Control-Allow-Credentials'=>'true',
		'Access-Control-Allow-Headers'=>'Content-Type, Authorization, X-Requested-With',
	]);
	$router=Router::startHttp('AppWeb');
    $router->bindResourceDir(APP_DIR.'/Routes');
	classloader(APP_DIR.'/Routes', '', '.php', $router->getMainNamespace(), true);
	$router->prepare();
	global $ROUTE;
	$ROUTE=$router->getRoute();
	unset($router);
	// Se encontró la función que se ejecutará
	// Aqui pueden realizar validaciones de seguridad antes de ejecutar el endpoint
	// Inicio de sesión, conexiones a bases de datos, u otros servicios externos que puedan retrazar la ejecución
	$result=$ROUTE->call();
	if(is_a($result, Response::class)){
		$result->send();
	}
}catch(RouteException $e){
	$e->getResponse()->send();
}
