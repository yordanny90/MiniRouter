# MiniRouter

Librería para el enrutamiento de los request entrantes y organización de código de los endpoints.

Funciona como la base para iniciar el desarrollo de un sitio Web, API, microservicio, o cualquier otro tipo de aplicación.
Ya que sienta las bases para todas solicitudes por HTTP e incluso la ejecución de jobs

## Requisitos mínimos

- PHP 7.1+ o 8.0+

---
## Hola mundo simple

Descarga el archivo [.MRcore.phar](.MRcore.phar) en la raíz de su servidor

Cear el archivo `index.php` con el siguiente contenido:
```PHP
define('APP_DIR', __DIR__.'/.myApp');
include ".MRcore.phar";
include "phar://.MRcore.phar/simple/router_web.php";
```

Crear el archivo `.myApp/endpoints/Web/index.php` con el siguiente contendo:
```PHP
<?php

namespace Web;
use MiniRouter\Response;

class index{
	static function GET_(...$args){
		?>
		<a href="/index.php/index/go">Go</a>
		<?php
	}
	static function GET_go(...$args){
		print_r($args);
		return Response::r_text('')->includeBuffer(1);
	}
}
```

Iniciar el servidor desde la carpeta raíz con el comando:
```shell
php -s localhost:8000 -t .
```

## Ejemplos avanzados

El código de los ejemplos está en:
- [Carpeta de ejemplos](.examples)
- [PHP de ejemplo1](example1.php)

Para ejecutar el cron(job) del ejemplo puede hacerlo con los comandos
```shell
php ./.examples/cron.php index
php ./.examples/cron.php index/explain/param1/param2 val1="Valor 1" "val2=Valor 2" -xy -z --FlagA --FlagB "Texto de prueba"
```

Si el ejemplo (web) no funciona correctamente, debe corregir la constante `BASE_URL` en [.examples/server/init.priv.php](.examples/server/init.priv.php)

## Que puedo modificar o borrar?

Cuando inicie un nuevo proyecto, recuerde eliminar los ejemplos.
También se recomienda eliminar la carpeta [.MRcore](.MRcore) ya que ese código se llamara directamente del archivo PHAR.

Solo el archivo [.MRcore.phar](.MRcore.phar) no debería ser alterado/eliminado para evitar problemas durante una actualización de MiniRouter.

Conserve solo el archivo PHAR que va a utilizar en su proyecto, la alternativa comprimida [.MRcore.phar.gz](.MRcore.phar.gz) puede reemplazar al anterior si desea ahorrar espacio de almacenamiento.

La carpeta [.myApp](.myApp) y su contenido, se pueden modificar segun la necesidad del desarrollo. Ya que su código y organización de carpetas, son solo un ejemplo de cómo debería ser usado MiniRouter.

Ya que el objetivo de [.myApp](.myApp) es solo almacenar código de la aplicación, se recomienda crear otras carpetas en la raíz del proyecto si tienen otros fines (como el almacenamiento de archivos de usuario)

El uso de la carpeta [.lib_class](.lib_class) es opcional. Ya que son librerías extra del proyecto, pero no son requeridas para su funcionamiento básico.
Puede habilitar el uso de esas librerías modificando el archivo [.myApp/init.php](.myApp/init.php)

---

### Esta versión aún está en desarrollo. ###
