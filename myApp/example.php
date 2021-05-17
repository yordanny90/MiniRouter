<?php
// Instrucciones globales que aplican para todos los request
use MiniRouter\Router;
use MiniRouter\Response;

ini_set('default_charset', 'utf-8');
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
}catch(\MiniRouter\RouterException $e){
	(new Response())->content('Internal error')->http_code(500)->send_exit();
}
// Opciones avanzadas del Router
//$router->main_namespace='EndPoint';
//$router->endpoint_file_suffix='.ep.php';
//$router->default_path='index';
//$router->max_subdir=1;
//$router->received_path=null;

/*
 * Devuelve una lista de los enpoints existentes (sin métodos ni las funciones en la clase)
 * Solo para pruebas, NO USAR EN EL AMBIENTE DE PRODUCCIÓN
 */
echo implode("\n", $router->scanEndpoints())."\n";

// Se ejecuta el Router del request entrante
try{
	$router->execHTTP();
}catch(\MiniRouter\BadRequestUrl $e){
	return (new Response())->content('Bad request url')->http_code(400)->send();
}catch(\MiniRouter\NotFoundException $e){
	(new Response())->content('Endpoint not found')->http_code(404)->send();
}catch(\MiniRouter\ParamMissingException $e){
	return (new Response())->content('Param url missing')->http_code(400)->send();
}catch(\MiniRouter\RouterException $e){
	(new Response())->content('Internal error')->http_code(500)->send();
}
//(new Response())->content('Method not allowed: '.$method)->http_code(405);
