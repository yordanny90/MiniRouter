<?php

namespace MiniRouter;

use Exception\BadRequestUrl;
use Exception\Execution;
use Exception\NotFound;
use Exception\ParamMissing;

class Router{
	/**
	 * @var string El directorio principal de la aplicación
	 */
	public static $app_dir;
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
	protected $main_namespace='Endpoint';
	/**
	 * @var string El directorio en el que se buscarán las clases de los endpoint
	 */
	protected $endpoint_dir;
	/**
	 * @var string Ruta del archivo del endpoint
	 */
	protected $_file;
	/**
	 * @var string Ruta básica para la clase del endpoint
	 */
	protected $_path_class;
	/**
	 * @var array Lista de parámetros después de la ruta de la clase
	 */
	protected $_params;
	protected $_endpointReady=false;
	/**
	 * @var array Partes de la ruta solicitada
	 */
	protected $route_parts=[];

	protected static function fixPath($path){
		return trim($path, " \t\n\r\0\x0B/");
	}

	/**
	 * Analiza la ruta para detectar el endpoint.<br>
	 * Si son válidos, guarda los valores en los parámetro de salida y devuelve true
	 * @param array $route_parts
	 * @throws Exception
	 */
	private function searchEndPoint(array $route_parts){
		$class='';
		$subdir=0;
		do{
			$add=array_shift($route_parts);
			if(is_null($add) || $add==='.' || $add==='..') throw new BadRequestUrl('Path not allowed: '.var_export($add, true));
			$class.='/'.$add;
			$file=$this->endpoint_dir.$class.static::$endpoint_file_suffix;
		}while(!is_file($file) && is_dir($this->endpoint_dir.$class) && count($route_parts)>0 && ++$subdir<=static::$max_subdir);
		if(!is_file($file)){
			throw new NotFound('File not found');
		}
		$this->_params=$route_parts;
		$this->_path_class=$class;
		$this->_file=$file;
	}

	/**
	 * @return mixed
	 */
	private function loadPHP(){
		return include_once $this->_file;
	}

	/**
	 * Router constructor.
	 * @param string $main_namespace
	 * @throws Exception
	 */
	public function __construct(string $main_namespace){
		$this->main_namespace=$main_namespace;
		$this->endpoint_dir=realpath(static::$app_dir.'/'.$main_namespace);
		if(!is_dir($this->endpoint_dir)){
			throw new Execution('Endpoint dir not found');
		}
	}

	/**
	 * @throws Exception
	 */
	public function prepareForHTTP(){
		$this->_endpointReady=false;
		if(Request::isCLI())
			throw new Execution('Execution by CLI is not allowed', 0);
		if(Response::headers_sent())
			throw new Execution('Headers has been sent', 0);
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
		$this->_endpointReady=false;
		if(!Request::isCLI()){
			throw new Execution('Only execution by CLI is allowed');
		}
		if(is_null(static::$received_path))
			static::$received_path=RequestCLI::getArg(1);
		if(is_null(static::$received_method))
			static::$received_method='CLI';
		$path=self::fixPath(static::$received_path);
		$this->route_parts=array_diff(explode('/', $path), ['', '.', '..']);
	}

	/**
	 * @throws Exception
	 */
	public function loadEndPoint(){
		$this->searchEndPoint($this->route_parts);
		$this->loadPHP();
		$this->_endpointReady=true;
	}

	/**
	 * Antes de llamarlo se requiere {@see Router::prepareForHTTP()} o {@see Router::prepareForCLI()}
	 * @return Route
	 * @throws Exception
	 */
	public function getRoute(){
		if(!$this->_endpointReady)
			throw new Execution('Endpoint not ready');
		$params=$this->_params;
		try{
			$route=Route::getRoute($this->main_namespace, $this->_path_class, static::$received_method, $params);
		}catch(\ReflectionException $e){
			throw new NotFound('Class not found', 0, $e);
		}
		if(is_null($route))
			throw new NotFound('Function not found');
		$param_missing=$route->getReqParams()-count($params);
		if($param_missing>0)
			throw new ParamMissing($param_missing);
		$route->exec_params=$params;
		return $route;
	}

}
