<?php

use MiniRouter\Router;
use MiniRouter\Response;

// Se carga la librería del MiniRouter
require_once __DIR__.'/init.php';
define('APP_HREF', APP_SCRIPT==DEFAULT_SCRIPT?'':APP_SCRIPT.'/');
define('APP_BASE_HREF', BASE_URL.rtrim(APP_HREF, '/'));
Response::flatBuffer();
try{
	// Opciones avanzadas del Router
	Response::addHeaders([
		'Access-Control-Allow-Origin'=>'*',
		'Access-Control-Allow-Credentials'=>'true',
		//	'Access-Control-Allow-Methods'=>'PUT, GET',
		'Access-Control-Allow-Headers'=>'Content-Type, Authorization, X-Requested-With',
		//	'Content-Type'=>'text/plain',
	]);
	$router=new Router('Web');
	\MiniRouter\classloader(APP_DIR.'/endpoints', '', '.php', $router->getMainNamespace(), true);
	//$router->default_path='index';
	//$router->missing_class='';
	//$router->max_subdir=1;
	//$router->received_path=Request::getPath();
	//$router->received_method=Request::getMethod();
	$router->prepareForHTTP();
	$router->loadEndPoint();
	// Se encontró la ruta del endpoint
	// Ahora que se encontró la ruta. Aqui puede realizar validaciones de seguridad antes de ejecutar el endpoint
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
