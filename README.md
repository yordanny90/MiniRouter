# MiniRouter

Librería para el enrutamiento de los request entrantes y organización de código de los endpoints.

Funciona como la base para iniciar el desarrollo de un sitio Web, API, microservicio, o cualquier otro tipo de aplicación.
Ya que sienta las bases para todas solicitudes por HTTP e incluso la ejecución de jobs

[Ir a ![GitHub CI](http://www.google.com/s2/favicons?domain=www.github.com)](https://github.com/yordanny90/MiniRouter)

## Requisitos mínimos

- PHP 7.1+ o 8.0+

---
## Estructura de clases

Con el fin de que funcione correctamente la carga automática de las clases, incluyendo los endpoints. Se deben organizar los archivos siguiendo una de los siguientes sintaxis en la ruta del archivo.

Los siguientes ejemplos aplican para un endpoint Web cuya clase es `index` con el namespace `\AppWeb\api\modulo`

    {APP_DIR}/Routes/AppWeb/api/modulo/index.php
    {APP_DIR}/Routes/AppWeb/api/modulo-index.php
    {APP_DIR}/Routes/AppWeb/api/modulo.index.php
    {APP_DIR}/Routes/AppWeb/api-modulo/index.php
    {APP_DIR}/Routes/AppWeb/api.modulo/index.php

Los siguientes ejemplos aplican para un endpoint Web cuya clase es `index` con el namespace `\AppWeb\api`

    {APP_DIR}/Routes/AppWeb/api/index.php
    {APP_DIR}/Routes/AppWeb/api-index.php
    {APP_DIR}/Routes/AppWeb/api.index.php

`{APP_DIR}` representa la constante que define la ruta de la aplicación

---
## Ejemplos

Hay varios modos de iniciar un proyecto, según el formato que desee en las direcciones.

La estructura de archivos y carpetas es indiferente, esta elección solo afecta las URLs

Para todos los ejemplos solo necesita descargar el archivo [.MRcore.phar](.MRcore.phar) en la raíz de su repositorio

### Hola mundo

Crear el archivo `index.php` que procesa todos los request al servidor
```PHP
define('APP_DIR', __DIR__.'/.example');
require ".MRcore.phar";
\MiniRouter\Sample::router_http();
```

Crear el archivo `.example/Routes/AppWeb/index.php` para establecer los endpoints `index` e `index.go` por metodo `GET`
```PHP
<?php

namespace AppWeb;

use MiniRouter\Response;

class index{
	static function GET_(){
		?>
		<h1><a href="/index.go/Hola mundo">Go</a></h1>
		<?php
	}

	static function GET_go($txt){
		echo $txt.'<br>';
		print_r($_SERVER);
		return Response::r_text('', true);
	}
}
```

Iniciar el servidor desde la carpeta raíz con el comando:
```shell
php -S localhost:8000 -F .
```

### Cambiar el separador de la ruta

Es posible utilizar otros separadores distintos al punto, como el `/`, sin embargo no se recomienda este caracter, ya que la optimización de búsqueda de las rutas.
Cualquier otros caracter tendra el mismo nivel de optimización que el punto, por ejemplo el guión `-`.

Sin embargo la cantidad de caracteres utilizados con este fin son limitados, vea la información en la documentación de la función `Router::setSplitter()`

Ahora vamos a crear el archivo `.example/router_http.php`, así:

```PHP
<?php
if(!defined('APP_DIR')) throw new Exception('App dir missing', 1);

use MiniRouter\Response;

if(\MiniRouter\Request::isCLI()){
	Response::r_forbidden()->content('Execution by CLI is not allowed')->send();
	exit;
}
try{
	Response::flatBuffer();
	Response::addHeaders([
		'Access-Control-Allow-Origin'=>'*',
		'Access-Control-Allow-Credentials'=>'true',
		'Access-Control-Allow-Headers'=>'Content-Type, Authorization, X-Requested-With',
	]);
	$router=\MiniRouter\Router::startHttp('AppWeb');
	$router->setSplitter('-'); // CAMBIO DE SEPARADOR
	\MiniRouter\classloader(APP_DIR.'/Routes', '', '.php', $router->getMainNamespace(), true);
	$router->prepare();
	global $ROUTE;
	$ROUTE=$router->getRoute();
	unset($router);
	$result=$ROUTE->call();
	if(is_a($result, Response::class)){
		$result->send();
	}
}catch(\MiniRouter\RouteException $e){
	$e->getResponse()->send();
}
```

El archivo `index.php` dejara de llamar a `\MiniRouter\Sample::router_http()`, y se pasará utilizar el php anterior, así:
```PHP
define('APP_DIR', __DIR__.'/.example');
require ".MRcore.phar";
//\MiniRouter\Sample::router_http();
require APP_DIR."/router_http.php";
```

Ahora en el archivo `.example/Routes/AppWeb/index.php`, para establecer los endpoints separados por `-` solo debemos cambiar los enlaces, así:

```PHP
<?php

namespace AppWeb;

use MiniRouter\Response;

class index{
	static function GET_(){
		// SOLO SE CAMBIA EL PUNTO POR UN "/"
		?>
		<h1><a href="/index-go/Hola mundo">Go</a></h1>
		<?php
	}

	static function GET_go($txt){
		echo $txt.'<br>';
		echo '<pre>'.print_r($_SERVER).'</pre>';
	}
}
```

Iniciar el servidor desde la carpeta raíz con el comando:
```shell
php -S localhost:8000 -F .
```

---
### Ejemplo myApp

La carpeta `.myApp` contiene un ejemplo mas desarrollado que el anterior

---
### Esta versión aún está en desarrollo. ###
