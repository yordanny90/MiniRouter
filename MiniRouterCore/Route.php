<?php

namespace MiniRouter;

/**
 * Class Route
 * @package MiniRouter
 */
class Route{
	/**
	 * Ruta válida del Endpoint
	 * @var string
	 */
	public $path;
	/**
	 * Nombre completo de la Clase a ejecutar
	 * @var string
	 */
	public $class;
	/**
	 * Metodo a ejecutar
	 * @var string
	 */
	public $function;
	/**
	 * @var int Cantidad de parámetros requeridos para ejecutar la función sin provocar fallos
	 */
	public $min_params;
	/**
	 * @var string Método que el request acepta (GET, POST, PUT, ...)
	 */
	public $method;
	/**
	 * Parametros para la funcón a ejecutar
	 * @var array
	 */
	public $params;
	/**
	 * Documentación de parametros para el metodo a ejecutar
	 * @var array
	 */
	public $doc_params;

	/**
	 * Convierte un método de una clase en una ruta. Si el método no es válido, devuelve NULL
	 * @param string $path Ruta inicial
	 * @param \ReflectionMethod $ref_fn
	 * @return Route|null
	 */
	private static function methodToRoute($path_class, \ReflectionMethod $ref_fn){
		if($ref_fn->isPublic() && !$ref_fn->isStatic() && preg_match('/([A-Z_]+)_(.*)/', $ref_fn->getName(), $parts)){
			$route=new self();
			$route->class=$ref_fn->getDeclaringClass()->getName();
			$route->function=$ref_fn->getName();
			$route->min_params=$ref_fn->getNumberOfRequiredParameters();
			$route->method=$parts[1];
			$route->path=$path_class.(strlen($parts[2])?'/'.$parts[2]:'');
			$route->doc_params='';
			foreach($ref_fn->getParameters() AS $ref_par){
				$type=$ref_par->getType();
				$route->doc_params.='/{'.($type?$type.':':'').$ref_par->getName().($ref_par->isVariadic()?'*':($ref_par->isOptional()?'?':'')).'}';
			}
			return $route;
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
		$class=str_replace('/', '\\', $namespace.$path_class);
		$ref_class=new \ReflectionClass($class);
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
	 * @param string $f_function Nombre de la función que se busca
	 * @param string $f_method Nombre del método del request
	 * @return Route|null
	 * @throws \ReflectionException
	 */
	public static function getRoute($namespace, $path_class, $f_function, $f_method){
		$class=str_replace('/', '\\', $namespace.$path_class);
		$ref_class=new \ReflectionClass($class);
		$ref_fn=$ref_class->getMethod($f_method.'_'.$f_function);
		if(preg_match('/([A-Z_]+)_(.*)/', $ref_fn->getName(), $parts)){
			if($route=self::methodToRoute($path_class, $ref_fn)){
				return $route;
			}
		}
		return null;
	}

}
