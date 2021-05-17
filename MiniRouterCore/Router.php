<?php

namespace MiniRouter;

/**
 * Clase de la que deben extender los controladores del sistema.
 * <b>NOTAS IMPORTANTES:</b><br>
 * Para ocultar el archivo PHP de la dirección URI, se recomienda usar la siguiente regla en el archivo <b>.htaccess</b>,<br>
 * en la misma carpeta del index.php que se quiere ocultar:
 * <code>
 * # Redirección para ocultar el archivo en la URL
 * RewriteEngine On
 * RewriteCond %{REQUEST_FILENAME} !-f
 * RewriteRule ^(.*)$ %{ENV:BASE}index.php/$0 [QSA,L]
 * </code>
 * Para bloquear el acceso a las controladores, se recomienda usar la siguiente regla en el archivo <b>.htaccess</b>,<br>
 * en la carpeta de los contorladores:
 * <code>
 * <IfModule authz_core_module>
 * Require all denied
 * </IfModule>
 * <IfModule !authz_core_module>
 * Deny from all
 * </IfModule>
 * </code>
 * Class Driver
 * @package MiniRouter
 */
class Router{
	/**
	 * @var string El namespace al que deben pertenecer las clases de los endpoint
	 */
	public $main_namespace='endpoint';
	/**
	 * @var string Sufijo de los archivos endpoint. Esta sufijo no se indica en {@see Router::$received_path}
	 */
	public $endpoint_file_suffix='.ep.php';
	/**
	 * @var string Dirección del enpoint utilizada si {@see Router::$received_path} esta vacío
	 */
	public $default_path='index';
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

	private $endpoint_dir;
	/**
	 * @var string Ruta del archivo del endpoint
	 */
	private $_file;
	/**
	 * @var string Ruta básica para la clase del endpoint
	 */
	private $_path_class;
	/**
	 * @var array Lista de parámetros después de la ruta de la clase
	 */
	private $_params;
	private $_endpointReady=false;

	protected static function fixPath($path){
		return trim($path, " \t\n\r\0\x0B/");
	}

	/**
	 * Analiza la ruta para detectar el endpoint.<br>
	 * Si son válidos, guarda los valores en los parámetro de salida y devuelve true
	 * @param $path
	 * @param null $_params
	 * @param null $_php_file
	 * @param null $_path_class
	 * @throws BadRequestUrl
	 * @throws NotFoundException
	 */
	private function searchEndPoint($path){
		$params=array_diff(explode('/', $path), ['']);
		$class='';
		$subdir=0;
		do{
			$add=array_shift($params);
			if(is_null($add) || $add==='.' || $add==='..') throw new BadRequestUrl('Path not allowed: '.var_export($add, true));
			$class.='/'.$add;
			$file=$this->endpoint_dir.$class.$this->endpoint_file_suffix;
		}while(!is_file($file) && is_dir($this->endpoint_dir.$class) && count($params)>0 && ++$subdir<=$this->max_subdir);
		if(!is_file($file)){
			throw new NotFoundException('File not found');
		}
		$this->_params=$params;
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
	 * @param $endpoint_dir
	 * @throws ExecException
	 */
	public function __construct($endpoint_dir){
		$this->endpoint_dir=realpath($endpoint_dir);
		if(!is_dir($this->endpoint_dir)){
			throw new ExecException('Enpoint dir not found');
		}
	}

	/**
	 * @throws BadRequestUrl
	 * @throws ExecException
	 * @throws NotFoundException
	 * @throws ParamMissingException
	 */
	public function prepareHTTP(){
		$this->_endpointReady=false;
		if(Request::isCLI())
			throw new ExecException('Execution by CLI is not allowed', 0);
		if(Response::headers_sent())
			throw new ExecException('Headers has been sent', 0);
		if(is_null($this->received_path))
			$this->received_path=Request::getPath();
		if(is_null($this->received_method))
			$this->received_method=Request::getMethod();
		$path=self::fixPath($this->received_path);
		if($path==='')
			$path=self::fixPath($this->default_path);
		$this->searchEndPoint($path);
		$this->loadPHP();
		$this->_endpointReady=true;
	}

	/**
	 * @throws BadRequestUrl
	 * @throws NotFoundException
	 * @throws ParamMissingException
	 * @throws ExecException
	 */
	public function prepareCLI(){
		$this->_endpointReady=false;
		if(!Request::isCLI()){
			throw new ExecException('Only execution by CLI is allowed');
		}
		if(is_null($this->received_path))
			$this->received_path=$_SERVER['argv'][1];
		if(is_null($this->received_method))
			$this->received_method='CLI';
		$path=self::fixPath($this->received_path);
		$this->searchEndPoint($path);
		$this->loadPHP();
		$this->_endpointReady=true;
	}

	/**
	 * Antes de llamarlo se requiere {@see Router::prepareHTTP()} o {@see Router::prepareCLI()}
	 * @return Route
	 * @throws ExecException
	 * @throws NotFoundException
	 * @throws ParamMissingException
	 */
	public function getRoute(){
		if(!$this->_endpointReady)
			throw new ExecException('Endpoint not ready');
		$function='';
		$params=$this->_params;
		$function_taken=false;
		if(count($params)){
			$function=strval(array_shift($params));
			$function_taken=true;
		}
		try{
			$route=Route::getRoute($this->main_namespace, $this->_path_class, $this->received_method, $function);
		}catch(\ReflectionException $e){
			throw new NotFoundException('Class not found', 0, $e);
		}
		if(is_null($route))
			throw new NotFoundException('Function not found');
		if($route->function==='' && $function_taken){
			array_unshift($params, $function);
		}
		$param_missing=$route->req_params-count($params);
		if($param_missing>0)
			throw new ParamMissingException($param_missing);
		$route->exec_params=$params;
		return $route;
	}

	/**
	 * Obtiene todas las rutas del endpoint actual<br>
	 * Antes de llamarlo se requiere {@see Router::prepareHTTP()} o {@see Router::prepareCLI()}
	 * @return Route[]
	 * @throws ExecException
	 * @throws NotFoundException
	 */
	public function dumpRoutes(){
		if(!$this->_endpointReady)
			throw new ExecException('Endpoint not ready');
		try{
			$routes=Route::getRoutes($this->main_namespace, $this->_path_class);
		}catch(\ReflectionException $e){
			throw new NotFoundException('Class not found', 0, $e);
		}
		return $routes;
	}

	public function dumpEndpoints(){
		$this->_scanEndpoints($all);
		return $all;
	}

	private function _scanEndpoints(&$all=[], $subdir='', $level=0){
		$suffix=$this->endpoint_file_suffix;
		$suffix_len=strlen($suffix);
		$dirsrc=opendir($this->endpoint_dir.$subdir);
		if($dirsrc){
			while($file=readdir($dirsrc)){
				if($file=='.' || $file=='..') continue;
				$class=($subdir.'/').substr($file, 0, -$suffix_len);
				if($class && is_file($this->endpoint_dir.$class.$suffix) && substr($file, -$suffix_len)==$suffix){
					$all[]=$class;
				}
				elseif(is_dir($this->endpoint_dir.$subdir.'/'.$file) && $level<$this->max_subdir){
					self::_scanEndpoints($all, $subdir.'/'.$file, $level+1);
				}
			}
			closedir($dirsrc);
		}
	}

}
