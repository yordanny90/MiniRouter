<?php

use MiniRouter\Route;
use MiniRouter\RouterDump;

try{
	$config=include _APPDIR_.'/dataset/task.php';
	$router=new RouterDump($config['namespace']);
	$router->prepareForCLI();
	if(empty(RouterDump::$received_path) || RouterDump::$received_path=='/'){
		$endpoints=$router->dumpClasses();
		array_walk($endpoints, function($v){
			echo $v.PHP_EOL;
		});
	}
	else{
		$router->loadEndPoint();
		$routes=$router->dumpRoutes(RouterDump::$received_method);
		array_walk($routes, function(Route $v){
			echo $v->path.$v->getUrlParams().PHP_EOL;
		});
	}
	exit;
}
catch(\MiniRouter\Exception $e){
	$e->getResponse()->send();
	exit;
}