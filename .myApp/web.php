<?php

use MiniRouter\Request;
use MiniRouter\Router;
use MiniRouter\Response;

// Se carga la librería del MiniRouter
require_once __DIR__.'/init.php';

if(DUMP_ENDPOINTS && Request::getMethod()=='DUMP'){
	include _APPDIR_.'/web_dump.php';
	return;
}
try{
	// Opciones avanzadas del Router
	//Router::$endpoint_file_prefix='';
	//Router::$endpoint_file_suffix='.php';
	//Router::$default_path='index';
	//Router::$missing_class='';
	//Router::$max_subdir=1;
	//Router::$received_path=Request::getPath();
	//Router::$received_method=Request::getMethod();
	$config=include _APPDIR_.'/dataset/web.php';
	Response::addHeaders($config['headers']??[]);
	$router=new Router($config['namespace']);
	$router->prepareForHTTP();
	$router->loadEndPoint();
	// Se encontró la ruta del endpoint
	// Ahora que se encontró la ruta. Aqui puede realizar validaciones de seguridad antes de ejecutar el endpoint
	$route=$router->getRoute();
	unset($config, $router);
	// Se encontró la función que se ejecutará
	// Ahora que la ejecución está preparada. Aqui puede realizar conexiones a bases de datos, inicio de sesión u otros servicios externos que puedan retrazar la ejecución
	Response::flatBuffer();
	$result=$route->call($route);
	if(is_a($result, Response::class)){
		$result->send();
	}
}catch(\MiniRouter\Exception $e){
	$e->getResponse()->send();
	exit;
}
