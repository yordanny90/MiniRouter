<?php
use MiniRouter\Router;

// Habilitarlo para el ambiente de producción
//error_reporting(0);

// Se carga la librería del MiniRouter
require_once __DIR__.'/init.php';

try{
	// Opciones avanzadas del Router
	//Router::default_path='index';
	//Router::max_subdir=1;
	//Router::received_path=null;
	//Router::received_method=null;
	classloader(__DIR__.'/endpoints', Router::$endpoint_file_suffix);
	if(\MiniRouter\RequestCLI::getArgFlag('DUMP')){
		$router=new \MiniRouter\RouterDump('\Task');
		$router->prepareForCLI();
		if(empty(Router::$received_path) || Router::$received_path=='/'){
			$endpoints=$router->dumpEndpoints(__DIR__.'/endpoints');
			$base=\MiniRouter\Request::getBaseURI(0, 1);
			array_walk($endpoints, function($v){
				echo $v.PHP_EOL;
			});
		}
		else{
			$router->loadEndPoint();
			$routes=$router->dumpRoutes();
			array_walk($routes, function(\MiniRouter\Route $v){
				echo $v->path.$v->getUrlParams().PHP_EOL;
			});
		}
		exit;
	}
	$router=new Router('\Task');
	$router->prepareForCLI();
	$router->loadEndPoint();
	// Se eoncontró la ruta del endpoint
	// Ya que se encontró la ruta. Aqui puede realizar validaciones de seguridad antes de ejecutar el endpoint
	$route=$router->getRoute();
	// Se eoncontró la función que se ejecutará
	$route->call();
}catch(\MiniRouter\Exception $e){
	$e->getResponse()->send(1);
}