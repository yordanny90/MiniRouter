<?php

namespace MiniRouter;

class Router{
	/**
	 * @var string Directorio de los archivos endpoint
	 */
	public static $endpoint_dir;
	/**
	 * @var string Sufijo de los archivos endpoint. Esta sufijo no se indica en {@see Router::$received_path}
	 */
	public static $endpoint_file_suffix='.php';
	/**
	 * @var string Dirección del enpoint utilizada si {@see Router::$received_path} esta vacío
	 */
	public static $default_path='index';
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
	 * @throws Exception
	 */
	public function __construct(string $main_namespace){
		$this->main_namespace=$main_namespace;
	}

	/**
	 * @throws Exception
	 */
	public function prepareForHTTP(){
		if(!is_null($this->_endpoint_class)){
			return;
		}
		if(Request::isCLI())
			throw new Exception(Exception::RESP_EXECUTION, 'Execution by CLI is not allowed');
		if(Response::headers_sent())
			throw new Exception(Exception::RESP_EXECUTION,'Headers has been sent');
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
	 * @throws Exception
	 */
	public function prepareForCLI(){
		if(!is_null($this->_endpoint_class)){
			return;
		}
		if(!Request::isCLI()){
			throw new Exception(Exception::RESP_EXECUTION,'Only execution by CLI is allowed');
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
		classloader(self::$endpoint_dir, self::$endpoint_file_suffix, $this->main_namespace);
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
		else{
			$this->_endpoint_class='';
		}
	}

	/**
	 * Antes de llamarlo se requiere {@see Router::loadEndPoint()}
	 * @return Route
	 * @throws Exception
	 */
	public function getRoute(){
		if(!$this->_endpoint_class)
			throw new Exception(Exception::RESP_NOTFOUND,'Class');
		$params=$this->_params;
		try{
			$route=Route::getRoute($this->main_namespace, $this->_endpoint_class, static::$received_method, $params);
		}catch(\ReflectionException $e){
			throw new Exception(Exception::RESP_NOTFOUND,'Class', 0, $e);
		}
		if(is_null($route))
			throw new Exception(Exception::RESP_NOTFOUND,'Function');
		$param_missing=$route->getReqParams()-count($params);
		if($param_missing>0)
			throw new Exception(Exception::RESP_MISSINGPARAM,$param_missing.' missing');
		$route->exec_params=$params;
		return $route;
	}

}
