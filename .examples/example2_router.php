<?php

use MiniRouter\Request;
use MiniRouter\Router;
use MiniRouter\Response;

// Se carga la librería del MiniRouter
require_once __DIR__.'/init.php';
define('APP_SCRIPT', 'example2.php');
define('APP_HREF', APP_SCRIPT.'/');
define('APP_BASE_HREF', BASE_URL.APP_SCRIPT);
Response::flatBuffer();
try{
	// Opciones avanzadas del Router
	//Router::$endpoint_file_prefix='';
	//Router::$endpoint_file_suffix='.php';
	//Router::$default_path='index';
	//Router::$missing_class='';
	//Router::$max_subdir=1;
	//Router::$received_path=Request::getPath();
	//Router::$received_method=Request::getMethod();
	Response::addHeaders([
		'Access-Control-Allow-Origin'=>'*',
		'Access-Control-Allow-Credentials'=>'true',
		//	'Access-Control-Allow-Methods'=>'PUT, GET',
		'Access-Control-Allow-Headers'=>'Content-Type, Authorization, X-Requested-With',
		//	'Content-Type'=>'text/plain',
	]);
	$router=new Router('example2');
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
