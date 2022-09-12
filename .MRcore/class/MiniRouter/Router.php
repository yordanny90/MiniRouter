<?php

namespace MiniRouter;

class Router{
	/**
	 * @var string Prefijo de los archivos endpoint. Este prefijo no se indica en {@see Router::$received_path}
	 */
	public static $endpoint_file_prefix='';
	/**
	 * @var string Sufijo de los archivos endpoint. Este sufijo no se indica en {@see Router::$received_path}
	 */
	public static $endpoint_file_suffix='.php';
	/**
	 * @var string Dirección del enpoint utilizada si {@see Router::$received_path} esta vacío
	 */
	public static $default_path='index';
	/**
	 * @var string Nombre de la clase que se carga cuando no se encuentra la solicitada
	 */
	public static $missing_class='';
	/**
	 * >Cuantas más subcarpetas se permitan, menor será la eficiencia de la búsqueda
	 * @var int Deterina el máximo de subcarpetas permitidos en la búsqueda del endpoint.<br>
	 * Cada subcarpeta debe ser parte del namespace de la clase del endpoint, empezando por {@see Router::$main_namespace}
	 */
	public static $max_subdir=1;
	/**
	 * @var string|null Indica la ruta recibida para la ejecución del endpoint. Si es null, el sistema la detectará auntomáticamente
	 */
	public static $received_path;
	/**
	 * @var string|null Indica el método por el que se ejecuta la ruta recibida. Si es null, el sistema la detectará auntomáticamente
	 */
	public static $received_method;

	/**
	 * @var string El namespace al que deben pertenecer las clases de los endpoint
	 */
	protected $main_namespace;
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
		return trim($path, " \t\n\r\0\x0B/");
	}

	/**
	 * Router constructor.
	 * @param string $main_namespace
	 * @throws RouteException
	 */
	public function __construct(string $main_namespace){
		$this->main_namespace=$main_namespace;
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
		if(is_null(static::$received_path))
			static::$received_path=Request::getPath();
		if(is_null(static::$received_method))
			static::$received_method=Request::getMethod();
		$path=self::fixPath(static::$received_path);
		if($path==='')
			$path=self::fixPath(static::$default_path);
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
		if(is_null(static::$received_path))
			static::$received_path=RequestCLI::getArgText(0);
		if(is_null(static::$received_method))
			static::$received_method='CLI';
		$path=self::fixPath(static::$received_path);
		$this->route_parts=array_diff(explode('/', $path), ['', '.', '..']);
	}

	public function loadEndPoint(){
		if(!is_null($this->_endpoint_class) || !is_array($this->route_parts)){
			return;
		}
		classloader(APP_DIR, self::$endpoint_file_prefix, self::$endpoint_file_suffix, $this->main_namespace);
		$route_parts=array_values($this->route_parts);
		$len=0;
		do{
			$class=$this->main_namespace.'\\'.implode('\\', array_slice($route_parts, 0, ++$len));
			if($class_found=class_exists($class)){
				$route_parts=array_slice($route_parts, $len);
				break;
			}
		}while(!$class_found && count($route_parts)>$len && $len<=static::$max_subdir);
		if($class_found){
			$this->_endpoint_class=$class;
			$this->_params=$route_parts;
		}
		elseif(class_exists($this->main_namespace.'\\'.static::$missing_class)){
			$this->_endpoint_class=$this->main_namespace.'\\'.static::$missing_class;
			$this->_params=array_values($this->route_parts);
		}
	}

	/**
	 * Antes de llamarlo se requiere {@see Router::loadEndPoint()}
	 * @return Route
	 * @throws RouteException
	 */
	public function getRoute(){
		if(!$this->_endpoint_class)
			throw new RouteException('Class', RouteException::CODE_NOTFOUND);
		$params=$this->_params;
		$route=Route::getRoute($this->main_namespace, $this->_endpoint_class, static::$received_method, $params);
		$param_missing=$route->getReqParams()-count($params);
		if($param_missing>0)
			throw new RouteException('Params missing: '.$param_missing, RouteException::CODE_NOTFOUND);
		elseif($route->getParams()<count($params) && !$route->isParamsInfinite()){
			throw new RouteException('Too many params', RouteException::CODE_NOTFOUND);
		}
		$route->exec_params=$params;
		return $route;
	}

}
