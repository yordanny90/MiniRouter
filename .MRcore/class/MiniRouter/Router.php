<?php

namespace MiniRouter;

class Router{
	/**
	 * @var string Dirección del enpoint utilizada si {@see Router::$received_path} esta vacío.
	 *
	 * Solo aplica para endpoints HTTP (@see Router::prepareForHTTP())
	 */
	public $default_path='index';
	/**
	 * @var string Nombre de la clase que se carga cuando no se encuentra la solicitada
	 */
	public $missing_class='';
	/**
	 * >Cuantas más subcarpetas se permitan, menor será la eficiencia de la búsqueda
	 * @var int Deterina el máximo de subcarpetas permitidos en la búsqueda del endpoint.<br>
	 * Cada subcarpeta debe ser parte del namespace de la clase del endpoint, empezando por {@see Router::$main_namespace}
	 */
	public $max_subdir=1;
	/**
	 * @var string|null Indica la ruta recibida para la ejecución del endpoint. Si es null, el sistema la detectará auntomáticamente
	 */
	public $received_path;
	/**
	 * @var string|null Indica el método por el que se ejecuta la ruta recibida. Si es null, el sistema la detectará auntomáticamente
	 */
	public $received_method;

	/**
	 * @var string El namespace al que deben pertenecer las clases de los endpoint
	 */
	protected $main_namespace;
	/**
	 * @var string Clase de la que deben extender los endpoint
	 */
	protected $parentClass;
	/**
	 * @var string Nombre completo de la clase del endpoint
	 */
	protected $_endpoint_class;
	/**
	 * @var array Lista de parámetros después de la ruta de la clase
	 */
	protected $_params;
	/**
	 * @var array Partes de la ruta solicitada
	 */
	protected $route_parts;

	protected static function fixPath($path){
		return trim($path, '/');
	}

	/**
	 * Router constructor.
	 * @param string $main_namespace
	 * @param string $parentClass Nombre de la clase o interface del que debe extender o implementar los endpoints
	 * @throws RouteException
	 */
	public function __construct(string $main_namespace, string $parentClass=''){
		$this->main_namespace=$main_namespace;
		$this->parentClass=$parentClass;
	}

	/**
	 * @return string
	 */
	public function getMainNamespace(): string{
		return $this->main_namespace;
	}

	/**
	 * @throws RouteException
	 */
	public function prepareForHTTP(){
		if(!is_null($this->_endpoint_class)){
			return;
		}
		if(Request::isCLI())
			throw new RouteException('Execution by CLI is not allowed', RouteException::CODE_EXECUTION);
		if(Response::headers_sent())
			throw new RouteException('Headers has been sent', RouteException::CODE_EXECUTION);
		if(is_null($this->received_path))
			$this->received_path=Request::getPath();
		if(is_null($this->received_method))
			$this->received_method=Request::getMethod();
		$path=self::fixPath($this->received_path);
		if($path==='')
			$path=self::fixPath($this->default_path);
		$this->route_parts=array_diff(explode('/', $path), ['', '.', '..']);
	}

	/**
	 * @throws RouteException
	 */
	public function prepareForCLI(){
		if(!is_null($this->_endpoint_class)){
			return;
		}
		if(!Request::isCLI()){
			throw new RouteException('Only execution by CLI is allowed', RouteException::CODE_EXECUTION);
		}
		if(is_null($this->received_path))
			$this->received_path=RequestCLI::getArgText(0);
		if(is_null($this->received_method))
			$this->received_method='CLI';
		$path=self::fixPath($this->received_path);
		$this->route_parts=array_diff(explode('/', $path), ['', '.', '..']);
	}

	public function loadEndPoint(){
		if(!is_null($this->_endpoint_class) || !is_array($this->route_parts)){
			return;
		}
		$route_parts=array_values($this->route_parts);
		$len=0;
		do{
			$class=$this->main_namespace.'\\'.implode('\\', array_slice($route_parts, 0, ++$len));
			if($class_found=class_exists($class)){
				$route_parts=array_slice($route_parts, $len);
				break;
			}
		}while(!$class_found && count($route_parts)>$len && $len<=$this->max_subdir);
		if($class_found){
			$this->_endpoint_class=$class;
			$this->_params=$route_parts;
		}
		elseif(class_exists($this->main_namespace.'\\'.$this->missing_class)){
			$this->_endpoint_class=$this->main_namespace.'\\'.$this->missing_class;
			$this->_params=array_values($this->route_parts);
		}
	}

	/**
	 * Antes de llamarlo se requiere {@see Router::loadEndPoint()}
	 * @param bool $strict_parrams Default: true. Valida que los parámetros recibidos no excedan los esperados.
	 *
	 * Aunque se deshabilite seguira validando que cumpla con los parámetros mínimos
	 * @return Route
	 * @throws RouteException
	 */
	public function getRoute(bool $strict_parrams=true){
		if(!$this->_endpoint_class)
			throw new RouteException('Class', RouteException::CODE_NOTFOUND);
		try{
			$ref_class=new \ReflectionClass($this->_endpoint_class);
		}catch(\ReflectionException $e){
			throw new RouteException('Class', RouteException::CODE_NOTFOUND, $e);
		}
		if($this->parentClass && !$ref_class->isSubclassOf($this->parentClass)) throw new RouteException('Invalid class', RouteException::CODE_NOTFOUND);
		if(!$ref_class->isInstantiable()){
			throw new RouteException('Class', RouteException::CODE_FORBIDDEN);
		}
		$path_class=static::class_to_path($this->main_namespace, $this->_endpoint_class);
		$params=array_values($this->_params);
		$f_method=$this->received_method;
		$name=$params[0]??null;
		$route=null;
		$allows=[];
		foreach($ref_class->getMethods(\ReflectionMethod::IS_PUBLIC) AS $ref_fn){
			if($m_parts=static::getMethodParts($ref_fn->getName())){
				if($m_parts['name']===$name){
					$allows[]=$m_parts['method'];
					if($m_parts['method']===$f_method){
						$route=Route::create($path_class, $ref_fn);
						if($route) array_shift($params);
					}
				}
				elseif($m_parts['name']===''){
					$allows[]=$m_parts['method'];
					if(!$route && $m_parts['method']===$f_method){
						$route=Route::create($path_class, $ref_fn);
					}
				}
			}
		}
		if(!$route){
			if(count($allows)>0) throw new RouteException($f_method, RouteException::CODE_METHODNOTALLOWED);
			throw new RouteException('Function', RouteException::CODE_NOTFOUND);
		}
		$route->setAllows($allows);
		$param_missing=$route->getReqParams()-count($params);
		if($param_missing>0) throw new RouteException('Params missing: '.$param_missing, RouteException::CODE_NOTFOUND);
		elseif($strict_parrams && $route->getParams()<count($params) && !$route->isParamsInfinite()){
			throw new RouteException('Too many params', RouteException::CODE_NOTFOUND);
		}
		$route->exec_params=$params;
		return $route;
	}

	/**
	 * @param string $main_namespace
	 * @param string $class
	 * @return array
	 * @throws RouteException
	 */
	public function getRouteList(){
		if(!$this->_endpoint_class)
			throw new RouteException('Class', RouteException::CODE_NOTFOUND);
		if($this->parentClass && !is_subclass_of($this->_endpoint_class, $this->parentClass)) throw new RouteException('Invalid class', RouteException::CODE_NOTFOUND);
		$main_namespace=$this->main_namespace;
		$class=$this->_endpoint_class;
		try{
			$ref_class=new \ReflectionClass($class);
		}catch(\ReflectionException $e){
			throw new RouteException('Class', RouteException::CODE_NOTFOUND, $e);
		}
		if(!$ref_class->isInstantiable()){
			throw new RouteException('Class', RouteException::CODE_FORBIDDEN);
		}
		$path_class=static::class_to_path($main_namespace, $class);
		$routes=[];
		foreach($ref_class->getMethods(\ReflectionMethod::IS_PUBLIC) AS $ref_fn){
			if($r=Route::create($path_class, $ref_fn)){
				$routes[]=$r;
			}
		}
		return $routes;
	}

	public static function class_to_path($main_namespace, $class){
		if(substr($class,0,strlen($main_namespace))==$main_namespace){
			$path_class=substr($class, strlen($main_namespace));
		}
		else{
			$path_class=$class;
		}
		return ltrim(str_replace('\\', '/', $path_class), '/');
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

}
