<?php
use MiniRouter\Router;
use MiniRouter\Response;

// Habilitarlo para el ambiente de producción
//error_reporting(0);

// Se carga la librería del MiniRouter
require_once __DIR__.'/../MiniRouterCore/autoloader.php';

Response::addHeaders([
	'Access-Control-Allow-Origin'=>'*',
	'Access-Control-Allow-Credentials'=>'true',
	'Access-Control-Allow-Methods'=>'PUT, GET, POST, DELETE, OPTIONS',
	'Access-Control-Allow-Headers'=>'Content-Type, Authorization, X-Requested-With',
	'Content-Type'=>'text/plain',
	'P3P'=>'CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"'
]);

try{
	$router=new Router(__DIR__.'/endpoint');
	// Opciones avanzadas del Router
	//$router->main_namespace='EndPoint';
	//$router->endpoint_file_suffix='.ep.php';
	//$router->default_path='index';
	//$router->max_subdir=1;
	//$router->received_path=null;
	//$router->received_method=null;
	$router->prepareHTTP();
	// Se eoncontró la ruta del endpoint
	// Ya que se encontró la ruta. Aqui puede realizar validaciones de seguridad antes de ejecutar el endpoint
	$route=$router->getRoute();
	// Se eoncontró la función que se ejecutará
	$route->call();
}catch(\MiniRouter\ExecException $e){
	(new Response())->content('Internal error')->http_code(500)->send_exit();
}catch(\MiniRouter\BadRequestUrl $e){
	(new Response())->content('Bad request url. '.$e->getMessage())->http_code(400)->send();
}catch(\MiniRouter\NotFoundException $e){
	(new Response())->content('Endpoint not found. '.$e->getMessage())->http_code(404)->send();
}catch(\MiniRouter\ParamMissingException $e){
	(new Response())->content('Param url missing. '.$e->getMessage())->http_code(400)->send();
}
