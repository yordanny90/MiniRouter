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
	protected $path;

	/**
	 * @var \ReflectionMethod
	 */
	protected $ref;
	/**
	 * @var bool Indica si la ejecución está en curso.
	 *
	 * Una vez terminada, vuelve a ser false
	 */
	protected $started=false;

	private $infinite_params;

	protected function __construct(\ReflectionMethod $ref){
		$this->ref=$ref;
	}

	/**
	 * @return string
	 */
	public function getPath(): string{
		return $this->path;
	}

	public function getClass(){
		return $this->ref->class;
	}

	protected function getInstance(...$args){
		$class=$this->getClass();
		return new $class(...$args);
	}

	public function getFunction(){
		return $this->ref->getName();
	}

	public function getReqParams(){
		return $this->ref->getNumberOfRequiredParameters();
	}

	public function getParams(){
		return $this->ref->getNumberOfParameters();
	}

	public function isParamsInfinite(){
		if(is_bool($this->infinite_params)) return $this->infinite_params;
		$this->infinite_params=false;
		foreach($this->ref->getParameters() AS $ref_par){
			if($ref_par->isVariadic()) return $this->infinite_params=true;
		}
		return $this->infinite_params;
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
	 * @throws RouteException
	 */
	public function call(...$args){
		if($this->started) return false;
		if(!$this->isCallable())
			throw new RouteException('The route cannot be executed', RouteException::CODE_EXECUTION);
		if($this->ref->isStatic()){
			$this->started=true;
			$res=forward_static_call_array([
				$this->getClass(),
				$this->getFunction()
			], $this->exec_params);
		}
		else{
			$this->started=true;
			$obj=$this->getInstance(...$args);
			$res=call_user_func_array([
				$obj,
				$this->getFunction()
			], $this->exec_params);
		}
		$this->started=false;
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
			$r->path=ltrim($path_class.(strlen($parts['name'])?'/'.$parts['name']:''), '/');
			return $r;
		}
		return null;
	}

	/**
	 * @param string $fnName
	 * @return array|null Si el nombre es válido devuelve un array con dos llaves: 'method' y 'name'
	 */
	public static function getMethodParts(string $fnName){
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
	 * @param string $main_namespace
	 * @param string $class
	 * @param string $f_method Nombre del método de request
	 * @param string $f_function Nombre de la función que se busca
	 * @param array $params
	 * @return Route
	 * @throws RouteException
	 */
	public static function getRoute($main_namespace, $class, $f_method, &$params){
		try{
			$ref_class=new \ReflectionClass($class);
		}catch(\ReflectionException $e){
			throw new RouteException('Class', RouteException::CODE_NOTFOUND, $e);
		}
		if(!$ref_class->isInstantiable()){
			throw new RouteException('Class', RouteException::CODE_FORBIDDEN);
		}
		$path_class=self::class_to_path($main_namespace, $class);
		$params=array_values($params);
		$name=$params[0]??null;
		$route=null;
		$allows=[];
		$forbidden=false;
		foreach($ref_class->getMethods() AS $ref_fn){
			if($m_parts=static::getMethodParts($ref_fn->getName())){
				if($m_parts['name']===$name){
					if($ref_fn->isPublic()) $allows[]=$m_parts['method'];
					if($m_parts['method']===$f_method){
						$route=static::methodToRoute($path_class, $ref_fn);
						if($route) array_shift($params);
						if(!$route) $forbidden=true;
					}
				}
				elseif($m_parts['name']===''){
					if($ref_fn->isPublic()) $allows[]=$m_parts['method'];
					if($m_parts['method']===$f_method){
						if(!$route){
							$route=static::methodToRoute($path_class, $ref_fn);
							if(!$route) $forbidden=true;
						}
					}
				}
			}
		}
		if(count($allows)>0){
			if($forbidden){
				throw new RouteException('Function', RouteException::CODE_FORBIDDEN);
			}
			$allows=array_unique($allows);
			Response::addHeaders([
				'Allow'=>implode(', ', $allows),
			]);
			if(!$route){
				throw new RouteException($f_method, RouteException::CODE_METHODNOTALLOWED);
			}
		}
		if(!$route){
			throw new RouteException('Function', RouteException::CODE_NOTFOUND);
		}
		return $route;
	}

}
