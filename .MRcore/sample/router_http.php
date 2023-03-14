<?php
if(!defined('APP_DIR')) throw new Exception('App dir missing', 1);

use MiniRouter\Response;

if(\MiniRouter\Request::isCLI()){
	Response::r_forbidden()->content('Execution by CLI is not allowed')->send();
	exit;
}
try{
	Response::flatBuffer();
	Response::addHeaders([
		'Access-Control-Allow-Origin'=>'*',
		'Access-Control-Allow-Credentials'=>'true',
		'Access-Control-Allow-Headers'=>'Content-Type, Authorization, X-Requested-With',
	]);
	$router=\MiniRouter\Router::startHttp('AppWeb');
	\MiniRouter\classloader(APP_DIR.'/Routes', '', '.php', $router->getMainNamespace(), true);
	$router->prepare();
	global $ROUTE;
	$ROUTE=$router->getRoute();
	unset($router);
	// Se encontró la función que se ejecutará
	// Ahora que la ejecución está preparada. Aqui puede realizar conexiones a bases de datos, inicio de sesión u otros servicios externos que puedan retrazar la ejecución
	$result=$ROUTE->call();
	if(is_a($result, Response::class)){
		$result->send();
	}
}catch(\MiniRouter\RouteException $e){
	$e->getResponse()->send();
}
