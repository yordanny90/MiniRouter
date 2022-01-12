<?php

use MiniRouter\Request;
use MiniRouter\Router;
use MiniRouter\Response;

// Habilitarlo para el ambiente de producción
//error_reporting(0);

// Se carga la librería del MiniRouter
require_once __DIR__.'/init.php';

Response::addHeaders([
	'Access-Control-Allow-Origin'=>'*',
	'Access-Control-Allow-Credentials'=>'true',
//	'Access-Control-Allow-Methods'=>'PUT, GET, POST, DELETE, OPTIONS',
	'Access-Control-Allow-Headers'=>'Content-Type, Authorization, X-Requested-With',
	'Content-Type'=>'text/plain',
	'P3P'=>'CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"',
]);

try{
	// Opciones avanzadas del Router
	//Router::$endpoint_file_suffix='.php';
	//Router::$default_path='index';
	//Router::$max_subdir=1;
	//Router::$received_path=Request::getPath();
	//Router::$received_method=Request::getMethod();
	classloader(__DIR__.'/endpoints', Router::$endpoint_file_suffix);
	# DUMP Endpoints
	if(Request::getMethod()=='DUMP'){
		$router=new \MiniRouter\RouterDump('\Web');
		$router->prepareForHTTP();
		if(Router::$received_path==''){
			$endpoints=$router->dumpEndpoints(__DIR__.'/endpoints');
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
	$router=new Router('\Web');
	$router->prepareForHTTP();
	$router->loadEndPoint();
	// Se encontró la ruta del endpoint
	// Ya que se encontró la ruta. Aqui puede realizar validaciones de seguridad antes de ejecutar el endpoint
	$route=$router->getRoute();
	// Se eoncontró la función que se ejecutará
	$route->call();
}catch(\MiniRouter\Exception $e){
	$e->getResponse()->send();
}
