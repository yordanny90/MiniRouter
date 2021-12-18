##Ejemplo 1
```PHP
namespace Endpoint;
class index{
    public function GET_(){
        (new \MiniRouter\Response('Esta es la página principal'))->send_exit();
    }
}
```
