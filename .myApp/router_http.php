<?php

use MiniRouter\Response;

// Habilitarlo para el ambiente de producción
//error_reporting(0);

// Se carga la librería del MiniRouter
require_once __DIR__.'/init.php';
Response::flatBuffer();
Response::tryGlobalGZ(true);
try{
	call_user_func(function(){
		$router=\MiniRouter\Router::startHttp('AppWeb');
		$router->setAllows([
			'HEAD',
			'OPTIONS',
			'GET',
			'POST',
			//'PUT',
			//'DELETE',
			//'FETCH',
		]);
		// cross-domain compatibility
		Response::addHeaders([
			'Access-Control-Allow-Origin'=>'*',
			'Access-Control-Allow-Credentials'=>'true',
			'Access-Control-Allow-Methods'=>implode(', ', $router->getAllows()),
			'Access-Control-Allow-Headers'=>'Content-Type, Authorization, X-Requested-With',
		]);
		$router->setClassException(MyRouteException::class);
		\MiniRouter\classloader(APP_DIR.'/Routes', '', '.php', $router->getMainNamespace(), true);
		class miEnrutador extends \MiniRouter\ReRouter{
			public $m;
			public $p;

			function change(string $method, string $path): bool{
				$this->m=null;
				$this->p=null;
				// Cambia una ruta de barras (/) por puntos (.), segun el segundo valor
				if(is_string($p=preg_replace('/^(\w+)\/(json|ini)(\b|\/)/', '$1.$2$3',$path, 1, $c)) && $c){
					$this->p=$p;
					return true;
				}
				return false;
			}

			public function getMethod(): ?string{
				return $this->m;
			}

			public function getPath(): ?string{
				return $this->p;
			}
		}
		$router->setReRouter(new miEnrutador());
		$router->prepare();
		######################################
		## Este código genera archivos JSON con todos los endpoints de la clase a la que se esta accediendo
		## No se recomienda activar este código en ambientes productivos
		/* */
		$listFile=APP_DIR.'/../.server/'.$router->getMainNamespace().'/'.$router->getClassPath().'.php';
		if(!is_file($listFile) || filemtime($listFile)!=filemtime($router->getClassFile())){
			if(!is_dir(dirname($listFile))) mkdir(dirname($listFile), 0777, true);
			$l=$router->getRouteList();
			DatasetExport::saveTo($listFile, $l, 'Último cambio: '.date(DATE_W3C));
			touch($listFile, filemtime($router->getClassFile()));
		}
		/* */
		######################################
		// Se encontró la ruta del endpoint
		// Ahora que se encontró la ruta. Aqui puede realizar validaciones de seguridad antes de ejecutar el endpoint
		if($router->getMethod()==='OPTIONS'){
			$allows=$router->getRouteAllow($router->getName());
			if(!in_array('OPTIONS', $allows)){
				(new Response())->headers([
					'Allow'=>implode(', ', $allows),
				])->send();
				return;
			}
		}
		if($router->getMethod()==='HEAD'){
			$allows=$router->getRouteAllow($router->getName());
			if(!in_array('HEAD', $allows)){
				if(in_array('GET', $allows)){
					(new Response())->send();
					return;
				}
				else{
					(new Response())->httpCode(404)->send();
					return;
				}
			}
		}
		global $ROUTE;
		$ROUTE=$router->getRoute();
		Response::addHeaders([
			'Allow'=>implode($router->getRouteAllow($router->getName())),
		]);
		unset($router);
		// Se encontró la función que se ejecutará
		// Ahora que la ejecución está preparada. Aqui puede realizar conexiones a bases de datos, inicio de sesión u otros servicios externos que puedan retrazar la ejecución
		$result=$ROUTE->call();
		if(is_a($result, Response::class)){
			$result->send();
		}
	});
}catch(\MiniRouter\RouteException $e){
	$result=$e->getResponse();
	$result->send();
}
