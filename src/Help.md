[repo]: https://github.com/yordanny90/MiniRouter
[iconGit]: http://www.google.com/s2/favicons?domain=www.github.com
# [MiniRouter ![GitHub CI][iconGit]][repo]
El MiniRouter se encarga del enrutamiento basado en clases debidamente organizadas en una carpeta de rutas.

### Requisitos mínimos

- PHP 7.1+ o 8.0+

---
## Estructura de clases
Con el fin de que funcione correctamente la carga automática de las clases, incluyendo los endpoints. Se deben organizar los archivos siguiendo una de los siguientes sintaxis en la ruta del archivo.

Los siguientes ejemplos aplican para un endpoint Web cuya clase es `index` con el namespace `\AppWeb\api\modulo`

    {APP_DIR}/Routes/AppWeb/api/modulo/index.php
    {APP_DIR}/Routes/AppWeb/api-modulo/index.php
    {APP_DIR}/Routes/AppWeb/api.modulo/index.php
    {APP_DIR}/Routes/AppWeb-api/modulo/index.php
    {APP_DIR}/Routes/AppWeb.api/modulo/index.php
    {APP_DIR}/Routes/AppWeb-api-modulo/index.php
    {APP_DIR}/Routes/AppWeb.api.modulo/index.php

Los siguientes ejemplos aplican para un endpoint Web cuya clase es `index` con el namespace `\AppWeb\api`

    {APP_DIR}/Routes/AppWeb/api/index.php
    {APP_DIR}/Routes/AppWeb-api/index.php
    {APP_DIR}/Routes/AppWeb.api/index.php

`{APP_DIR}` representa la constante que define la ruta de la aplicación

---

Puede consultar las posibles rutas para el archivo de una clase con la función `\MiniRouter\class_search_file_list`

Las rutas para la clase `\AppWeb\api\index` del ejemplo anterior se pueden encontrar así:
```php
$array_files=\MiniRouter\class_search_file_list(\AppWeb\api\index::class, APP_DIR.'/Routes');
```

## Cómo funcionan las rutas HTTP
Por defecto, el enrutamiento funciona mediante una organizacion de rutas de archvos, nombres de clases y nombres de funciones.

Según la ruta del `PATH_INFO` y el método HTTP, el nombre de la clase y el nombre de la función pública dentro de la clase son obtenidas.
Si la clase o la función no existen o son inaccesibles, el estado de la respuesta será un HTTP 4xx.

Un ejemplo simple siendo que el método HTTP es `GET` y el PATH_INFO es `/account`, la clase `AppWeb\account` ejecutará la función pública `GET_`

Si al ejemplo anterior solo le cambiamos el método a `POST`, la clase `AppWeb\account` ejecutará la función pública `POST_`

### Ejemplos de rutas
Para rutas más complejas la clase y el método a ejecutar se determina según el caracter separador.

El caracter separador por defecto es `.` (punto)

Para el método HTTP es `GET` y el PATH_INFO es `/account.info`, solo hay dos posibles funciones:
1. `AppWeb\account::GET_info`
2. `AppWeb\account\info::GET_`

Para el método HTTP es `POST` y el PATH_INFO es `/account.change/password`, solo hay dos posibles funciones:
1. `AppWeb\account::POST_change`. Con los parametros: `password`
2. `AppWeb\account\change::POST_`. Con los parametros: `password`

## Cambiar el separador de la ruta
La cantidad de caracteres utilizados con este fin son limitados, vea la información en la documentación de la función `Router::setSplitter()`

Es posible utilizar otros separadores distintos al punto, como el `/`, sin embargo **no se recomienda este caracter**, ya que la optimización de búsqueda de las rutas puede verse afectada.

Cualquier otro caracter distinto al `/` tendra el mismo nivel de optimización que el punto.

> **IMPORTANTE:** Elija el caracter separador al iniciar su proyecto, ya que al cambiarlo altera todas las urls de su aplicación

---
Asi se cambia el caracter separador a un `/` (no recomendado):

```PHP
$router=\MiniRouter\Router::startHttp('AppWeb');
$router->setSplitter('/'); // CAMBIO DE SEPARADOR ANTES DE PREPARE
$router->prepare();
```

Esto afecta los siguientes ejemplos:

Para el método HTTP es `GET` y el PATH_INFO es `/account/info`, hay tres posibles ejecuciones.
Ordenado por prioridad, iniciando de inmediatamente la clase que se encuentre primero:
1. `AppWeb\account::GET_info`
2. `AppWeb\account::GET_`. Con los parametros: `info`
3. `AppWeb\account\info::GET_`

Para el método HTTP es `POST` y el PATH_INFO es `/account/change/password`, hay cinco posibles ejecuciones.
Ordenado por prioridad, iniciando de inmediatamente la clase que se encuentre primero:
1. `AppWeb\account::POST_change`. Con los parametros: `password`
2. `AppWeb\account::POST_`. Con los parametros: `change`, `password`
3. `AppWeb\account\change::POST_password`
4. `AppWeb\account\change::POST_`. Con los parametros: `password`
5. `AppWeb\account\change\password::POST_`

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
## Redirigir rutas
El comportamiento descrito anteriormente se puede alterar creando una clase que extienda de `\MiniRouter\ReRouter`

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
Para evitar conflictos de compatibilidad con NGINX, la lectura del `PATH_INFO` ya está solucionado por el siguiente código:
```PHP
$path_info=\MiniRouter\Request::getPathInfo();
```
