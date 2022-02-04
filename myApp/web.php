<?php

use MiniRouter\Request;
use MiniRouter\Router;
use MiniRouter\Response;

// Habilitarlo para el ambiente de producción
//error_reporting(0);

// Se carga la librería del MiniRouter
require_once __DIR__.'/init.php';

$config=\MiniRouter\Dataset::getData('EndpointsWeb');
Response::addHeaders($config['headers']);

try{
	// Opciones avanzadas del Router
	//Router::$endpoint_file_suffix='.php';
	//Router::$default_path='index';
	//Router::$max_subdir=1;
	//Router::$received_path=Request::getPath();
	//Router::$received_method=Request::getMethod();
	Router::$endpoint_dir=__DIR__;
	# DUMP Endpoints
	if(Request::getMethod()=='DUMP'){
		$router=new \MiniRouter\RouterDump($config['namespace']);
		$router->prepareForHTTP();
		if(Router::$received_path==''){
			$endpoints=$router->dumpClasses();
			$base=Request::getBaseURI(0, 1);
			Response::json([
				'base'=>$base,
				'endpoints'=>$endpoints
			])->send_exit();
		}
		else{
			$router->loadEndPoint();
			$routes=$router->dumpRoutes();
			$base=Request::getBaseURI(0, 1);
			array_walk($routes, function(\MiniRouter\Route &$v){
				$v->method=$v->getMethod();
				$v->params=$v->getUrlParams();
			});
			Response::json([
				'base'=>$base,
				'routes'=>$routes
			])->send_exit();
		}
		return;
	}
	$router=new Router($config['namespace']);
	$router->prepareForHTTP();
	$router->loadEndPoint();
	// Se encontró la ruta del endpoint
	// Ahora que se encontró la ruta. Aqui puede realizar validaciones de seguridad antes de ejecutar el endpoint
	$route=$router->getRoute();
	// Se encontró la función que se ejecutará
	// Ahora que la ejecución está preparada. Aqui puede realizar conexiones a bases de datos, inicio de sesión u otros servicios externos que puedan retrazar la ejecución
	ob_start();
	unset($config, $router);
	$result=$route->call();
	if(is_a($result, Response::class)){
		$result->send();
	}
}catch(\MiniRouter\Exception $e){
	$e->getResponse()->send_exit();
}
