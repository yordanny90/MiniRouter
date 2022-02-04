<?php
use MiniRouter\Router;

// Habilitarlo para el ambiente de producción
//error_reporting(0);

// Se carga la librería del MiniRouter
require_once __DIR__.'/init.php';

$config=\MiniRouter\Dataset::getData('EndpointsTask');

try{
	// Opciones avanzadas del Router
	//Router::$endpoint_file_suffix='.php';
	//Router::$default_path='index';
	//Router::$max_subdir=1;
	//Router::$received_path=null;
//	Router::$received_method='CLI';
	Router::$endpoint_dir=__DIR__;
	if(\MiniRouter\RequestCLI::getArgFlag('DUMP')){
		$router=new \MiniRouter\RouterDump($config['namespace']);
		$router->prepareForCLI();
		if(empty(Router::$received_path) || Router::$received_path=='/'){
			$endpoints=$router->dumpClasses();
			$base=\MiniRouter\Request::getBaseURI(0, 1);
			array_walk($endpoints, function($v){
				echo $v.PHP_EOL;
			});
		}
		else{
			$router->loadEndPoint();
			$routes=$router->dumpRoutes(Router::$received_method);
			array_walk($routes, function(\MiniRouter\Route $v){
				echo $v->path.$v->getUrlParams().PHP_EOL;
			});
		}
		exit;
	}
	$router=new Router($config['namespace']);
	$router->prepareForCLI();
	$router->loadEndPoint();
	// Se encontró la ruta del endpoint
	// Ya que se encontró la ruta. Aqui puede realizar validaciones de seguridad antes de ejecutar el endpoint
	$route=$router->getRoute();
	// Se encontró la función que se ejecutará
	$result=$route->call($router, $route);
}catch(\MiniRouter\Exception $e){
	$e->getResponse()->send(1);
}