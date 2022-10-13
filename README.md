# MiniRouter
Librería para el enrutamiento de los request entrantes y organización de código de los endpoints.

Funciona como la base para iniciar el desarrollo de un sitio Web, API, microservicio, o cualquier otro tipo de aplicación.
Ya que sienta las bases para todas solicitudes por HTTP e incluso la ejecución de jobs

## Requisitos mínimos

- PHP 7.1+ o 8.0+

---
## Ejemplos

El código de los ejemplos está en:
- [Carpeta de ejemplos](.examples)
- [PHP de ejemplo1](example1.php)

Para ejecutar el cron(job) del ejemplo puede hacerlo con los comandos
```shell
php ./.examples/cron.php index
php ./.examples/cron.php index/explain/param1/param2 val1="Valor 1" "val2=Valor 2" -xy -z --FlagA --FlagB "Texto de prueba"
```

Si el ejemplo (web) no funciona correctamente, debe corregir la constante `BASE_URL` en [.examples/server/init.priv.php](.examples/server/init.priv.php)

Puede eliminarlos para limpiar su proyecto.

## Que puedo modificar?

Solo el archivo [.MRcore.phar.gz](.MRcore.phar.gz) no debería ser alterada para evitar problemas durante una actualización de MiniRouter.

La carpeta [.myApp](.myApp) y su contenido, se pueden modificar segun la necesidad del desarrollo. Ya que su código y organización de carpetas, son un ejemplo de cómo debería ser usado MiniRouter.

Ya que el objetivo de [.myApp](.myApp) es solo almacenar código de la aplicación, se recomienda crear otras carpetas en la raíz del proyecto con otros fines (como el almacenamiento de archivos)

El uso de la carpeta [.lib_class](.lib_class) es opcional. Ya que son librerías extra del proyecto, pero no son requeridas para su funcionamiento básico.
Puede habilitar el uso de esas librerías modificando el archivo [.myApp/init.php](.myApp/init.php)

---

### Esta versión aún está en desarrollo. ###
