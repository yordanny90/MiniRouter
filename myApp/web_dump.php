<?php
// Se carga la librería del MiniRouter
require_once __DIR__.'/init.php';

\MiniRouter\Response::addHeaders([
	'Access-Control-Allow-Origin'=>'*',
	'Access-Control-Allow-Credentials'=>'true',
	'Access-Control-Allow-Methods'=>'PUT, GET, POST, DELETE, OPTIONS',
	'Access-Control-Allow-Headers'=>'Content-Type, Authorization, X-Requested-With',
	'Content-Type'=>'text/html',
	'P3P'=>'CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"',
]);

try{
	// Opciones avanzadas del Router
	//Router::$endpoint_file_suffix='.php';
	//Router::$default_path='index';
	//Router::$max_subdir=1;
	//Router::$received_path=Request::getPath();
	//Router::$received_method=Request::getMethod();
	$router=new \MiniRouter\RouterDump('Endpoint');
	$router->prepareForHTTP();
	if(\MiniRouter\Router::$received_path==''){
		$endpoints=$router->dumpEndpoints();
		$base=\MiniRouter\Request::getBaseURI(0, 1);
		array_walk($endpoints, function($v) use ($base){
			?>
			<div>
				<a href="<?=$base.$v?>" target="dump"><?=$base.$v?></a>
			</div>
			<?php
		});
	}
	else{
		$router->loadEndPoint();
		$routes=$router->dumpRoutes();
		$base=\MiniRouter\Request::getBaseURI(0, 1);
		array_walk($routes, function(\MiniRouter\Route $v) use ($base){
			?>
			<div>
				<b><?=htmlentities($v->getMethod().' '.$base.$v->path.$v->getUrlParams())?></b>
				<pre><?php
					print_r($v);
					?></pre>
			</div>
			<?php
		});
	}
}catch(\MiniRouter\Exception $e){
	$e->getResponse()->send();
}