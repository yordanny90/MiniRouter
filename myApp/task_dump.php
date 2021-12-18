<?php
// Se carga la librería del MiniRouter
require_once __DIR__.'/init.php';

try{
	// Opciones avanzadas del Router
	//Router::$endpoint_file_suffix='.php';
	//Router::$default_path='index';
	//Router::$max_subdir=1;
	//Router::$received_path=null;
	//Router::$received_method=null;
	$router=new \MiniRouter\RouterDump('Task');
	$router->prepareForCLI();
	if(\MiniRouter\Router::$received_path==''){
		$endpoints=$router->dumpEndpoints();
		$base=\MiniRouter\Request::getBaseURI(0, 1);
		array_map(function($v) use ($base){
			echo $v.PHP_EOL;
		}, $endpoints);
		exit;
	}
	else{
		$router->loadEndPoint();
		$routes=$router->dumpRoutes();
		array_map(function(\MiniRouter\Route $v){
			echo $v->path.$v->getUrlParams().PHP_EOL;
		}, $routes);
		exit;
	}
}catch(\MiniRouter\Exception $e){
	$e->getResponse()->send();
}