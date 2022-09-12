<?php

use MiniRouter\RequestCLI;
use MiniRouter\Router;

// Habilitarlo para el ambiente de producción
//error_reporting(0);

// Se carga la librería del MiniRouter
require_once __DIR__.'/init.php';

if(DUMP_ENDPOINTS && RequestCLI::getArgFlag('DUMP')){
	include APP_DIR.'/task_dump.php';
	return;
}
try{
	// Opciones avanzadas del Router
	//Router::$endpoint_file_prefix='';
	//Router::$endpoint_file_suffix='.php';
	//Router::$default_path='index';
	//Router::$missing_class='';
	//Router::$max_subdir=1;
	//Router::$received_path=null;
	//Router::$received_method='CLI';
	$config=include APP_DIR.'/dataset/task.php';
	$router=new Router($config['namespace']);
	unset($config);
	$router->prepareForCLI();
	$router->loadEndPoint();
	// Se encontró la ruta del endpoint
	// Ya que se encontró la ruta. Aqui puede realizar validaciones de seguridad antes de ejecutar el endpoint
	$route=$router->getRoute();
	unset($config, $router);
	// Se encontró la función que se ejecutará
	$result=$route->call($route);
}catch(\MiniRouter\RouteException $e){
	$e->getResponse()->send();
}