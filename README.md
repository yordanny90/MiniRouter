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

    Por carpetas:   {APP_DIR}/endpoints/Web/api/modulo/index.php
    Por guiones:    {APP_DIR}/endpoints/Web-api-modulo/index.php
    Por puntos:     {APP_DIR}/endpoints/Web.api.modulo/index.php

Los ejemplos aplican para un endpoint Web cuya clase es `index` con el namespace `\Web\api\modulo`

`{APP_DIR}` representa la constante que define la ruta de la aplicación

---
## Ejemplos

Hay dos modos de iniciar un proyecto, según el formato que desee en las direcciones: `Route`, `RouteP`.

La estructura de archivos y carpetas es indiferente, esta elección solo afecta las URLs

Para todos los ejemplos solo necesita descargar el archivo [.MRcore.phar](.MRcore.phar) en la raíz de su repositorio

### Hola mundo (Route)

Crear el archivo `index.php` que procesa todos los request al servidor
```PHP
define('APP_DIR', __DIR__.'/.myApp');
include ".MRcore.phar";
include "phar://.MRcore.phar/simple/router_web.php";
```

Crear el archivo `.myApp/endpoints/Web/index.php` para establecer los endpoints `index` e `index/go` por metodo `GET`
```PHP
<?php

namespace Web;

use MiniRouter\Response;

class index{
	static function GET_(){
		?>
		<h1><a href="<?=$_SERVER['SCRIPT_NAME']?>/index/go/Hola mundo">Go</a></h1>
		<?php
	}

	static function GET_go($txt){
		echo $txt.'<br>';
		print_r($_SERVER);
		return Response::r_text('')->includeBuffer(1);
	}
}
```

Iniciar el servidor desde la carpeta raíz con el comando:
```shell
php -s localhost:8000 -F index.php
```

### Hola mundo (RouteP)

Crear el archivo `index.php` que procesa todos los request al servidor:
```PHP
define('APP_DIR', __DIR__.'/.myApp');
include ".MRcore.phar";
include "phar://.MRcore.phar/simpleP/router_web.php";
```

Crear el archivo `.myApp/endpoints/Web/index.php` para establecer los endpoints `index` e `index.go` por metodo `GET`
```PHP
<?php

namespace Web;

use MiniRouter\Response;

class index{
	static function GET_(){
		?>
		<h1><a href="<?=$_SERVER['SCRIPT_NAME']?>/index.go/Hola mundo">Go</a></h1>
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
php -s localhost:8000 -F index.php
```

---
### Esta versión aún está en desarrollo. ###
