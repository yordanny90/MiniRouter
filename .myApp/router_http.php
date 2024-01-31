<?php

use MiniRouter\Response;

require_once __DIR__.'/init.php';
if(\MiniRouter\Request::isCLI()){
	Response::r_forbidden()->content('Execution by CLI is not allowed')->send();
	exit;
}
try{
	if(!define('APP_SCRIPT', \MiniRouter\Request::getScript())) throw new Exception('APP_SCRIPT already loaded', 1);
	if(!define('HREF', basename(APP_SCRIPT).'/')) throw new Exception('HREF already loaded', 1);
	Response::flatBuffer();
	Response::tryGlobalGZ(true);
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

		class miEnrutador implements \MiniRouter\ReRouter{
			public $p;

			function change(string $path): bool{
				$this->m=null;
				$this->p=null;
				// Cambia una ruta de barras (/) por puntos (.), segun el segundo valor
				if(is_string($p=preg_replace('/^(\w+)\/(json|ini)(\/.*)?$/', '$1.$2$3', $path, 1, $c)) && $c){
					$this->p=$p;
					return true;
				}
				return false;
			}

			public function newPath(): ?string{
				return $this->p;
			}
		}

		$router->setReRouter(new miEnrutador());
		$router->prepare();
		######################################
		// Se encontró la ruta del endpoint
		// Ahora que se encontró la ruta. Aqui puede realizar validaciones de seguridad antes de ejecutar el endpoint
        if($router->getMethod()==='OPTIONS' && in_array($router->getMethod(), $router->getAllows())){
            $allows=$router->getRouteAllow($router->getName());
            if(!in_array('OPTIONS', $allows)){
                Response::r_empty()->headers([
                    'Allow'=>implode(', ', $allows)
                ])->send();
                return;
            }
        }
		global $ROUTE;
		$ROUTE=$router->getRoute();
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
