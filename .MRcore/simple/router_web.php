<?php
if(!defined('APP_DIR')) throw new Exception('App dir missing', 1);
use MiniRouter\Router;
use MiniRouter\Response;
try{
	Response::addHeaders([
		'Access-Control-Allow-Origin'=>'*',
		'Access-Control-Allow-Credentials'=>'true',
		'Access-Control-Allow-Headers'=>'Content-Type, Authorization, X-Requested-With',
	]);
	$router=new Router('Web');
	\MiniRouter\classloader(APP_DIR.'/endpoints', '', '.php', $router->getMainNamespace(), true);
	if(defined('RECEIVED_PATH')) $router->received_path=RECEIVED_PATH;
	$router->prepareForHTTP();
	$router->loadEndPoint();
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
	exit;
}
