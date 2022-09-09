<?php

use MiniRouter\Route;
use MiniRouter\RouterDump;
use MiniRouter\Response;

try{
	$config=include APP_DIR.'/dataset/web.php';
	$router=new RouterDump($config['namespace']);
	$router->prepareForHTTP();
	if(RouterDump::$received_path==''){
		$endpoints=$router->dumpClasses();
		unset($config, $router);
		Response::r_json([
			'base'=>BASE_URL,
			'endpoints'=>$endpoints
		])->send();
		exit;
	}
	else{
		$router->loadEndPoint();
		$routes=$router->dumpRoutes();
		unset($config, $router);
		array_walk($routes, function(Route &$v){
			$v->method=$v->getMethod();
			$v->params=$v->getUrlParams();
		});
		Response::r_json([
			'base'=>BASE_URL,
			'routes'=>$routes
		])->send();
		exit;
	}
}catch(\MiniRouter\RouteException $e){
	$e->getResponse()->send();
	exit;
}