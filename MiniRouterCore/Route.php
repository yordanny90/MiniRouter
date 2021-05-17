<?php

namespace MiniRouter;

/**
 * Class Route
 * @package MiniRouter
 * @property array $exec_params
 */
class Route{
	/**
	 * @var string Nombre completo de la Clase a ejecutar
	 */
	public $class;
	/**
	 * @var string Metodo a ejecutar
	 */
	public $function;
	/**
	 * @var int Cantidad de parámetros requeridos para ejecutar la función sin provocar fallos
	 */
	public $req_params;
	/**
	 * @var string Método que el request acepta (GET, POST, PUT, ...)
	 */
	public $method;
	/**
	 * @var string Ruta válida del Endpoint
	 */
	public $path;
	/**
	 * @var string Documentación de parametros para el método a ejecutar
	 */
	public $path_doc;

	/**
	 * @var Route|null
	 */
	private static $thisRoute;

	protected function __construct(){ }

	/**
	 * @return bool
	 */
	public function isCallable(){
		return isset($this->exec_params) && is_array($this->exec_params);
	}

	/**
	 * Ejecuta este ruta
	 * @return bool|mixed
	 */
	public function call(){
		if(!$this->isCallable() && !self::$thisRoute)
			return false;
		self::$thisRoute=&$this;
		$object=new $this->class();
		if($this->function===''){
			return call_user_func_array($object, $this->exec_params);
		}
		else{
			return call_user_func_array([
				$object,
				$this->function
			], $this->exec_params);
		}
	}

	/**
	 * Instancia actual en ejecucióm, si existe
	 * @return Route|null
	 */
	public static function this(){
		return self::$thisRoute;
	}

	/**
	 * Convierte un método de una clase en una ruta. Si el método no es válido, devuelve NULL
	 * @param string $path_class Ruta inicial de la clase
	 * @param \ReflectionMethod $ref_fn
	 * @return Route|null
	 */
	private static function methodToRoute($path_class, \ReflectionMethod $ref_fn){
		if($ref_fn->isPublic() && !$ref_fn->isStatic()){
			if($ref_fn->getName()==='__invoke'){
				$r=new self();
				$r->class=$ref_fn->getDeclaringClass()->getName();
				$r->function=$ref_fn->getName();
				$r->req_params=$ref_fn->getNumberOfRequiredParameters();
				$r->method='*';
				$r->path=$path_class;
				$r->path_doc=$r->path;
				$i=0;
				foreach($ref_fn->getParameters() AS $ref_par){
					$r->path_doc.='/{'.$ref_par->getName().($ref_par->isVariadic()?'*':($i>=$r->req_params?'?':'')).'}';
					++$i;
				}
				return $r;
			}
			if($ref_fn->getName()==='__call'){
				$r=new self();
				$r->class=$ref_fn->getDeclaringClass()->getName();
				$r->function=$ref_fn->getName();
				$r->req_params=0;
				$r->method='*';
				$r->path=$path_class;
				$r->path_doc=$r->path;
				return $r;
			}
			if(preg_match('/([A-Z]+)_(.*)/', $ref_fn->getName(), $parts)){
				$r=new self();
				$r->class=$ref_fn->getDeclaringClass()->getName();
				$r->function=$ref_fn->getName();
				$r->req_params=$ref_fn->getNumberOfRequiredParameters();
				$r->method=$parts[1];
				$r->path=$path_class.(strlen($parts[2])?'/'.$parts[2]:'');
				$r->path_doc=$r->path;
				$i=0;
				foreach($ref_fn->getParameters() AS $ref_par){
					$r->path_doc.='/{'.$ref_par->getName().($ref_par->isVariadic()?'*':($i>=$r->req_params?'?':'')).'}';
					++$i;
				}
				return $r;
			}
		}
		return null;
	}

	/**
	 * @param string $namespace
	 * @param string $path
	 * @return Route[]
	 * @throws \ReflectionException
	 */
	public static function getRoutes($namespace, $path_class){
		$className=str_replace('/', '\\', $namespace.$path_class);
		$ref_class=new \ReflectionClass($className);
		$routes=[];
		foreach($ref_class->getMethods(\ReflectionMethod::IS_PUBLIC) AS $ref_fn){
			if($route=self::methodToRoute($path_class, $ref_fn)){
				$routes[]=$route;
			}
		}
		return $routes;
	}

	/**
	 * @param string $namespace
	 * @param string $path
	 * @param string $f_method Nombre del método de request
	 * @param string $f_function Nombre de la función que se busca
	 * @return Route|null
	 * @throws \ReflectionException
	 */
	public static function getRoute($namespace, $path_class, $f_method, $f_function){
		$className=str_replace('/', '\\', $namespace.$path_class);
		$ref_class=new \ReflectionClass($className);
		try{
			$ref_fn=$ref_class->getMethod($f_method.'_'.$f_function);
			$route=self::methodToRoute($path_class, $ref_fn);
			return $route;
		}catch(\ReflectionException $e){
		}
		try{
			$ref_fn=$ref_class->getMethod('__call');
			$route=self::methodToRoute($path_class, $ref_fn);
			$route->method=$f_method;
			$route->function=$f_method.'_'.$f_function;
			$route->path.='/'.$f_function;
			$route->path_doc.='/'.$f_function;
			return $route;
		}catch(\ReflectionException $e){
		}
		try{
			$ref_fn=$ref_class->getMethod('__invoke');
			$route=self::methodToRoute($path_class, $ref_fn);
			$route->method=$f_method;
			$route->function='';
			return $route;
		}catch(\ReflectionException $e){
		}
		return null;
	}

}
