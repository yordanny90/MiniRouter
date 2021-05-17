##Ejemplo 1
```PHP
namespace endpoint;
class index{
    public function GET_(){
        (new \MiniRouter\Response('Esta es la página principal'))->send_exit();
    }
}
```
