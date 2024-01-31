### Esta versión aún está en desarrollo. ###

# MiniRouter
Librería para el enrutamiento de los request entrantes y organización de código de los endpoints.

Funciona como la base para iniciar el desarrollo de un sitio Web, API, microservicio, o cualquier otro tipo de aplicación.
Ya que sienta las bases para todas solicitudes por HTTP y la ejecución por CLI.

No requiere que se registren las rutas en un archivo, solo cree una nueva clase y tendra una nueva ruta.

### [Ver documentación](src/Help.md)
MiniRouter no pretende ser un framework, ya que no incluye ninguna librería adicional, como autenticación o conexión a bases de datos.

Pero si puedes utilizarlo para crear el framework a tu medida. 
Ya que es lo suficientemente flexible para cambiar todo su comportamiento. 
Incluso puede ser incluido, de muchas formas, dentro de un proyecto preexistente sin que se vea afectado.

## Ejemplos
Hay varios modos de iniciar un proyecto, según el formato que desee en las direcciones.

La estructura de archivos y carpetas es indiferente, esta elección solo afecta las URLs

Para todos los ejemplos solo necesita descargar el archivo [MRcore.phar](build/MRcore.phar) 
en una carpeta de su repositorio que no sea accesible para el usuario.

Por ejemplo la carpeta `.lib` que esta protegida por el [.htaccess](.htaccess)

### Ejemplo: Hola mundo
Crear el archivo `index.php` que recibe todos los request al servidor.
Esto solo se programa al iniciar el proyecto, la lógica de la aplicación se realizará en otros archivos.
```PHP
define('APP_DIR', __DIR__.'/.myApp');
require ".lib/MRcore.phar";
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

---
### Ejemplo myApp
La carpeta `.myApp` en repositorio contiene un ejemplo mas desarrollado que el anterior
