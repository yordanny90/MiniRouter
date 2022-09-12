# MiniRouter
Librería para el enrutamiento de los request entrantes y organización de código de los endpoints.

Funciona como la base para iniciar el desarrollo de un sitio Web, API, microservicio, o cualquier otro tipo de aplicación.
Ya que sienta las bases para todas solicitudes por HTTP e incluso la ejecución de jobs

---
## Ejemplos

El código de los ejemplos está en:
- [ejemplo1](.example1)
- [ejemplo2](.example2)
- [PHP de ejemplo1](example.php)

Puede eliminarlos para limpiar su proyecto.

Para probar el ejemplo de la carpeta [ejemplo1](.example1) puede comentar el `include` en el [index.php](index.php) y agregar:
```php
include BASE_DIR.'/.example1/web.php';
```

---
## Que puedo modificar?

Solo la carpeta [.MRcore](.MRcore) no debería ser alterada para evitar problemas durante una actualización de MiniRouter.

Las carpetas [.myApp](.myApp) y [.shared](.shared), y su contenido, se pueden modificar segun la necesidad del desarrollo. Ya que su código y organización de carpetas, son un ejemplo de cómo debería ser usado MiniRouter.

---

### Esta versión aún está en desarrollo. ###
