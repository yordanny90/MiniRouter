<?php
use MiniRouter\Router;

// Habilitarlo para el ambiente de producción
//error_reporting(0);

// Se carga la librería del MiniRouter
require_once __DIR__.'/init.php';

try{
	// Opciones avanzadas del Router
	//Router::endpoint_file_suffix='.php';
	//Router::default_path='index';
	//Router::max_subdir=1;
	//Router::received_path=null;
	//Router::received_method=null;
	$router=new Router('Task');
	$router->prepareForCLI();
	$router->loadEndPoint();
	// Se eoncontró la ruta del endpoint
	// Ya que se encontró la ruta. Aqui puede realizar validaciones de seguridad antes de ejecutar el endpoint
	$route=$router->getRoute();
	// Se eoncontró la función que se ejecutará
	$route->call();
}catch(\MiniRouter\Exception $e){
	$e->getResponse()->send();
}