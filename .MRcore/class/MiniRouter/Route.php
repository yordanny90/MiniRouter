<?php
namespace MiniRouter;

/**
 * Class Route
 * @package MiniRouter
 * @property array $exec_params
 */
class Route{
	/**
	 * @var string Ruta válida del Endpoint
	 */
	public $path;

	/**
	 * @var \ReflectionMethod
	 */
	protected $ref;

	protected function __construct(\ReflectionMethod $ref){
		$this->ref=$ref;
	}

	public function getClass(){
		return $this->ref->class;
	}

	public function getInstance(...$args){
		$class=$this->getClass();
		return new $class(...$args);
	}

	public function getFunction(){
		return $this->ref->getName();
	}

	public function getReqParams(){
		return $this->ref->getNumberOfRequiredParameters();
	}

	public function getUrlParams(){
		$url_params='';
		$req_params=$this->getReqParams();
		$i=0;
		foreach($this->ref->getParameters() AS $ref_par){
			$url_params.='/{'.$ref_par->getName().($ref_par->isVariadic()?'*':($i>=$req_params?'?':'')).'}';
			++$i;
		}
		return $url_params;
	}

	public function getMethod(){
		return static::getMethodParts($this->getFunction())['method'];
	}

	/**
	 * @return bool
	 */
	public function isCallable(){
		return isset($this->exec_params) && is_array($this->exec_params);
	}

	/**
	 * Ejecuta esta ruta
	 * @param mixed ...$args Parámetros que recibe el constructor de la clase
	 * @return false|mixed
	 * @throws Exception
	 */
	public function call(...$args){
		if(!$this->isCallable())
			throw new \AppException(\AppException::RESP_EXECUTION, 'The route cannot be executed');
		if($this->ref->isStatic()){
			$res=forward_static_call_array([
				$this->getClass(),
				$this->getFunction()
			], $this->exec_params);
		}
		else{
			$obj=$this->getInstance(...$args);
			$res=call_user_func_array([
				$obj,
				$this->getFunction()
			], $this->exec_params);
		}
		return $res;
	}

	public static function class_to_path($main_namespace, $class){
		if(substr($class,0,strlen($main_namespace))==$main_namespace){
			$path_class=substr($class, strlen($main_namespace));
		}
		else{
			$path_class=$class;
		}
		return $path_class;
	}

	/**
	 * Convierte un método de una clase en una ruta. Si el método no es válido, devuelve NULL
	 * @param string $path_class Ruta inicial de la clase
	 * @param \ReflectionMethod $ref_fn
	 * @return Route|null
	 */
	private static function methodToRoute($path_class, \ReflectionMethod $ref_fn){
		$path_class=str_replace('\\', '/', $path_class);
		if($ref_fn->isPublic() && ($parts=static::getMethodParts($ref_fn->getName()))){
			$r=new static($ref_fn);
			$r->path=$path_class.(strlen($parts['name'])?'/'.$parts['name']:'');
			return $r;
		}
		return null;
	}

	/**
	 * @param string $fnName
	 * @return array|null Si el nombre es válido devuelve un array con dos llaves: 'method' y 'name'
	 */
	public static function getMethodParts(string $fnName): ?array{
		if(preg_match('/^([A-Z]+)_(.*)$/', $fnName, $matches)){
			return [
				'method'=>$matches[1],
				'name'=>$matches[2]
			];
		}
		return null;
	}

	/**
	 * @param string $class
	 * @param null $method
	 * @return array
	 * @throws \ReflectionException
	 */
	public static function getRoutes($main_namespace, $class, $method=null){
		$routes=[];
		$ref_class=new \ReflectionClass($class);
		if(!$ref_class->isInstantiable()){
			return $routes;
		}
		$path_class=self::class_to_path($main_namespace, $class);
		foreach($ref_class->getMethods(\ReflectionMethod::IS_PUBLIC) AS $ref_fn){
			if($route=static::methodToRoute($path_class, $ref_fn)){
				if(is_string($method) && $route->getMethod()!=$method) continue;
				$routes[]=$route;
			}
		}
		return $routes;
	}

	/**
	 * @param string $path
	 * @param string $f_method Nombre del método de request
	 * @param string $f_function Nombre de la función que se busca
	 * @return Route|null
	 * @throws \ReflectionException
	 * @throws Exception
	 */
	public static function getRoute($main_namespace, $class, $f_method, &$params){
		$ref_class=new \ReflectionClass($class);
		if(!$ref_class->isInstantiable()){
			return null;
		}
		$path_class=self::class_to_path($main_namespace, $class);
		$params=array_values($params);
		$name='';
		if(isset($params[0])){
			$name=$params[0];
			try{
				$ref_fn=$ref_class->getMethod($f_method.'_'.$name);
				$route=static::methodToRoute($path_class, $ref_fn);
				array_shift($params);
				return $route;
			}catch(\ReflectionException $e){
			}
		}
		try{
			$ref_fn=$ref_class->getMethod($f_method.'_');
			$route=static::methodToRoute($path_class, $ref_fn);
			return $route;
		}catch(\ReflectionException $e){
		}
		foreach($ref_class->getMethods(\ReflectionMethod::IS_PUBLIC) AS $ref_fn){
			if($ref_fn->isPublic() && ($m_parts=static::getMethodParts($ref_fn->getName()))){
				if($m_parts['name']=='' || $m_parts['name']==$name){
					throw new \AppException(\AppException::RESP_METHODNOTALLOWED,$f_method);
				}
			}
		}
		return null;
	}

}
