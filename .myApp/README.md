[Volver](../README.md)
## Ejemplos

El código de los ejemplos consiste en:
- [Carpeta de ejemplos](.)
- [PHP Web](index.php)
- [PHP CLI](task.php)

Para ejecutar el job del ejemplo puede hacerlo con los comandos
```shell
php ./task.php args
php ./task.php globals
php ./task.php args.show/param1/param2 -var1:"Valor 1" "-var2:Valor 2" -Flags? "-Texto de prueba 1"  --FlagA --FlagB "Texto de prueba 2"
```

## Que puedo modificar o borrar de este repositorio?

Solo el archivo [.MRcore.phar](../.MRcore.phar) es indispensable para una actualización exitosa de MiniRouter.

Conserve solo el archivo PHAR que va a utilizar en su proyecto, la alternativa comprimida [.MRcore.phar.gz](../.MRcore.phar.gz) puede reemplazar al anterior si desea ahorrar espacio de almacenamiento.

También se elimina la carpeta [src](../src), es innecesario mantenerlo ya que ese código ya está incluido en el archivo PHAR.

La carpeta [.myApp](.) se puede modificar segun la necesidad del desarrollo, ya que su código y organización de carpetas, son solo un ejemplo de cómo debería ser usado MiniRouter.

La carpeta [.myApp](.) es definida en la constante`APP_DIR`, por lo que puede cambiar este directorio según lo necesite.

Ya que el objetivo de `APP_DIR` es solo almacenar código de la aplicación, se recomienda crear otras carpetas en la raíz del proyecto si tienen otros fines (como el almacenamiento de archivos de usuario)

Puede habilitar la carga automática de otras carpetas usando la función `\MiniRouter\classloader()`, como se hace en [.myApp/init.php](init.php)
