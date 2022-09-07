##Ejemplo 1
```PHP

namespace Web;

use MiniRouter\Response;

class index{
    public function GET_(){
        return Response::text('Esta es la página principal');
    }
}
```
