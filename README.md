[repo]: https://github.com/yordanny90/MiniRouter
[iconGit]: http://www.google.com/s2/favicons?domain=www.github.com

### Esta versión aún está en desarrollo. ###

# MiniRouter

Librería para el enrutamiento de los request entrantes y organización de código de los endpoints.

Funciona como la base para iniciar el desarrollo de un sitio Web, API, microservicio, o cualquier otro tipo de aplicación.
Ya que sienta las bases para todas solicitudes por HTTP e incluso la ejecución de jobs

[Ir a ![GitHub CI][iconGit]][repo]

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

## Cómo funcionan las rutas HTTP

Por defecto, el enrutamiento funciona mediante una organizacion de rutas de archvos, nombres de clases y nombres de funciones.

Según la ruta del `PATH_INFO` y el método HTTP, el nombre de la clase y el nombre de la función pública dentro de la clase son obtenidas.
Si la clase o la función no existen o son inaccesibles, el estado de la respuesta será un HTTP 404.

Un ejemplo simple siendo que el método HTTP es `GET` y el PATH_INFO es `/account`, solo hay una posible ejecución.
1. La clase `AppWeb\account` ejecutará la función pública `GET_`

Si al ejemplo anterior solo le cambiamos el método a `POST`, 
1. la clase `AppWeb\account` ejecutará la función pública `POST_`

---
Si ampliamos los casos configurando el router con el separador `/` así:
```PHP
$router=\MiniRouter\Router::startHttp('AppWeb');
$router->setSplitter('/');
```
Esto afecta los siguientes ejemplos:

Para el método HTTP es `GET` y el PATH_INFO es `/account/info`, hay tres posibles ejecuciones.
Ordenado por prioridad, iniciando de inmediatamente la clase que se encuentre primero:
1. Clase `AppWeb\account` ejecutará la función pública `GET_info`
2. Clase `AppWeb\account` ejecutará la función pública `GET_`
3. Clase `AppWeb\account\info` ejecutará la función pública `GET_`

Para el método HTTP es `POST` y el PATH_INFO es `/account/change/password`, hay cinco posibles ejecuciones.
Ordenado por prioridad, iniciando de inmediatamente la clase que se encuentre primero:
1. Clase `AppWeb\account` ejecutará la función pública `POST_change`. Con los parametros: `password`
2. Clase `AppWeb\account` ejecutará la función pública `POST_`. Con los parametros: `change`, `password`
3. Clase `AppWeb\account\change` ejecutará la función pública `POST_password`
4. Clase `AppWeb\account\change` ejecutará la función pública `POST_`. Con los parametros: `password`
5. Clase `AppWeb\account\change\password` ejecutará la función pública `POST_`

---
Por otro lado, si conservamos el router con el separador por defecto `.`, esto afecta los siguientes ejemplos:

Para el método HTTP es `GET` y el PATH_INFO es `/account.info/a/b`, hay dos posibles ejecuciones.
Ordenado por prioridad, iniciando de inmediatamente la clase que se encuentre primero:
1. Clase `AppWeb\account` ejecutará la función pública `GET_info`. Con los parametros: `a`, `b`
2. Clase `AppWeb\account\info` ejecutará la función pública `GET_`. Con los parametros: `a`, `b`

Para el método HTTP `POST` y el PATH_INFO es `/account.change.password/a/b`, hay dos posibles ejecuciones.
Ordenado por prioridad, iniciando de inmediatamente la clase que se encuentre primero:
1. Clase `AppWeb\account\change` ejecutará la función pública `POST_password`. Con los parametros: `a`, `b`
2. Clase `AppWeb\account\change\password` ejecutará la función pública `POST_`. Con los parametros: `a`, `b`

---
El comportamiento descrito en los ejemplos anteriores se puede alterar creando una clase que extienda de `\MiniRouter\ReRouter`

Por ejemplo. Si queremos conservar el separador por defecto `.`, pero permitir que algunas URLs se separen por `/`, la clase `miEnrutador` hace los siguientes cambios:
1. El PATH_INFO `account/info/...` se convertirá en `account.info/...`
2. El PATH_INFO `account/change/password/...` se convertirá en `account.change.password/...`
3. El PATH_INFO `.../processExport.json` se convertirá en `processExport.json/...`

```PHP
<?php
class miEnrutador implements \MiniRouter\ReRouter{
    public $p;

    public function change(string $path): bool{
        $this->p=null;
        // Cambia una ruta de barras (/) por puntos (.), segun el segundo valor
        if(preg_match('/^account\/info(\/.*)?$/', $path, $m)){
            $this->p='account.info'.($m[1]??'');
            return true;
        }
        if(preg_match('/^account\/change\/password(\/.*)?$/', $path, $m)){
            $this->p='account.change.password'.($m[1]??'');
            return true;
        }
        if(preg_match('/^(.*)\/processExport\.json$/', $path, $m)){
            $this->p='data.json/'.$m[1];
            return true;
        }
        return false;
    }

    public function newPath(): ?string{
        return $this->p;
    }
}
```

Luego crea un objeto de la clase `miEnrutador` para usarlo en el router:
```PHP
<?php
$router=\MiniRouter\Router::startHttp('AppWeb');
$router->setReRouter(new miEnrutador());
```

## PATH_INFO en NGINX

Para evitar problemas de compatibilidad con NGINX, la lectura del `PATH_INFO` ya está solucionado por el siguiente código:
```PHP
$path_info=\MiniRouter\Request::getPathInfo();
```

---
## Ejemplos

Hay varios modos de iniciar un proyecto, según el formato que desee en las direcciones.

La estructura de archivos y carpetas es indiferente, esta elección solo afecta las URLs

Para todos los ejemplos solo necesita descargar el archivo [.MRcore.phar](.MRcore.phar) en la raíz de su repositorio

### Ejemplo: Hola mundo

Crear el archivo `index.php` que recibe todos los request al servidor.
Esto solo se programa al iniciar el proyecto, la lógica de la aplicación se realizará en otros archivos.
```PHP
define('APP_DIR', __DIR__.'/.myApp');
require ".MRcore.phar";
\MiniRouter\Sample::router_http();
```

Crear el archivo `.myApp/Routes/AppWeb/index.php` para establecer el endpoint `index` por metodo HTTP `GET`, mediante una clase con el mimso nombre del archivo (`index`) y una función llamada `GET_`.
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
		return Response::r_text('', true);
	}
}
```

Iniciar el servidor de prueba desde la carpeta raíz con el comando:
```shell
php -S localhost:8000 -F .
```

### Cambiar el separador de la ruta

Es posible utilizar otros separadores distintos al punto, como el `/`, sin embargo no se recomienda este caracter, ya que la optimización de búsqueda de las rutas.
Cualquier otros caracter tendra el mismo nivel de optimización que el punto, por ejemplo el guión `-`.

Sin embargo la cantidad de caracteres utilizados con este fin son limitados, vea la información en la documentación de la función `Router::setSplitter()`

Ahora vamos a crear el archivo `.myApp/router_http.php`, así:

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
define('APP_DIR', __DIR__.'/.myApp');
require ".MRcore.phar";
//\MiniRouter\Sample::router_http();
require APP_DIR."/router_http.php";
```

Ahora en el archivo de la ruta `.myApp/Routes/AppWeb/index.php`, para establecer los endpoints separados por `-` solo debemos cambiar los enlaces, así:

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

Iniciar el servidor de prueba desde la carpeta raíz con el comando:
```shell
php -S localhost:8000 -F .
```

---
### Ejemplo myApp

La carpeta `.myApp` en repositorio contiene un ejemplo mas desarrollado que el anterior
