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
	 * @var int Máximo de subniveles de carpetas permitidos en los endpoint. Cada subcarpeta se agrega al {@see Router::$main_namespace}
	 */
	public $max_subdir=1;
	/**
	 * @var string|null Indica la ruta recibida para la ejecución del endpoint. Si es null, el sistema la detectará auntomáticamente
	 */
	public $received_path=null;

	protected $endpoint_dir;
	/**
	 * Ruta válida del Endpoint
	 * @var string
	 */
	protected $_path;
	/**
	 * Ruta válida del Endpoint
	 * @var string
	 */
	protected $_path_class;
	/**
	 * PHP del Endpoint a ejecutar
	 * @var string
	 */
	protected $_php_file;
	/**
	 * Nombre completo de la Clase a ejecutar
	 * @var string
	 */
	protected $_class;
	/**
	 * Metodo a ejecutar
	 * @var string
	 */
	protected $_function;
	/**
	 * Parametros para el metodo a ejecutar
	 * @var array
	 */
	protected $_params;

	protected static function fixPath($path){
		return trim($path, " \t\n\r\0\x0B/");
	}


	/**
	 * Analiza la ruta y si es válida, la guarda en {@see Router::$_path} y devuelve NULL
	 * @param string $path
	 * @return Response|void
	 */
	protected function analizePath($path, Route &$route){
		if($path==='')
			throw new NotFoundException();
		$route->path=$path;
	}

	/**
	 * Analiza los parametros para detectar el endpoint.<br>
	 * Si son validos, guarda
	 * @param $path
	 * @throws BadRequestUrl
	 * @throws NotFoundException
	 */
	protected function searchEndPoint($path){
		$params=array_diff(explode('/', $path), ['']);
		$class='';
		$subdir=0;
		do{
			$add=array_shift($params);
			if(is_null($add) || $add==='.' || $add==='..') throw new BadRequestUrl();
			$class.='/'.$add;
			$file=$this->endpoint_dir.$class.$this->endpoint_file_suffix;
		}while(!is_file($file) && is_dir($this->endpoint_dir.$class) && count($params)>0 && ++$subdir<=$this->max_subdir);
		if(!is_file($file)){
			throw new NotFoundException();
		}
		$this->_path_class=$class;
		$this->_params=$params;
		$this->_php_file=$file;
		return $class;
//		$function=strval(array_shift($params));
//		$this->_class=str_replace('/', '\\', $this->main_namespace.$class);
//		$this->_function=Request::getMethod().'_'.$function;
	}

	/**
	 * @param $filePHP
	 * @return mixed|Response
	 */
	private static function loadPHP($filePHP){
		return include($filePHP);
	}

	/**
	 * @return mixed
	 * @throws BadRequestUrl
	 * @throws NotFoundException
	 * @throws ParamMissingException
	 */
	private function exec(){
		$route=$this->prepareRoute();
		$obj=new $route->class();
		return call_user_func_array(array(
			$obj,
			$route->function
		), $route->params);
	}

	/**
	 * @throws BadRequestUrl
	 * @throws NotFoundException
	 * @throws ParamMissingException
	 * @throws RouterException
	 */
	public function execHTTP(){
		if(Request::isCLI()){
			throw new RouterException('Execution by CLI is not allowed', 0);
		}
		if(Response::headers_sent()){
			throw new RouterException('Headers has been sent', 0);
		}
		if(is_null($this->received_path))
			$this->received_path=Request::getPath();
		$this->exec();
	}

	/**
	 * @throws BadRequestUrl
	 * @throws NotFoundException
	 * @throws ParamMissingException
	 * @throws RouterException
	 */
	public function execCLI(){
		if(!Request::isCLI()){
			throw new RouterException('Only execution by CLI is allowed');
		}
		if(is_null($this->received_path))
			$this->received_path=$_SERVER['argv'][1];
		$this->exec();
	}

	/**
	 * Router constructor.
	 * @param $endpoint_dir
	 * @throws RouterException
	 */
	public function __construct($endpoint_dir){
		$this->endpoint_dir=realpath($endpoint_dir);
		if(!is_dir($this->endpoint_dir)){
			throw new RouterException('Enpoint dir not found');
		}
	}

	public function scanEndpoints(){
		$this->_scanEndpoints($all);
		return $all;
	}

	private function _scanEndpoints(&$all=[], $withHost=true, $subdir='', $level=0){
		$suffix=$this->endpoint_file_suffix;
		$suffix_len=strlen($suffix);
		foreach(scandir($this->endpoint_dir.$subdir) AS $file){
			if($file=='.' || $file=='..') continue;
			$class=($subdir.'/').substr($file, 0, -$suffix_len);
			if($class && is_file($this->endpoint_dir.$class.$suffix) && substr($file, -$suffix_len)==$suffix){
				$all[]=Request::getBaseHref($withHost).$class;
			}
			elseif(is_dir($this->endpoint_dir.$subdir.'/'.$file) && $level<$this->max_subdir){
				self::_scanEndpoints($all, $withHost, $subdir.'/'.$file, $level+1);
			}
		}
	}

	/**
	 * Analiza la ruta y devuelve una ruta lista para ser ejecutada
	 * @return Route
	 * @throws BadRequestUrl
	 * @throws NotFoundException
	 * @throws ParamMissingException
	 */
	private function prepareRoute(){
		# Analisa y establece el Path
		$path=self::fixPath($this->received_path);
		if($path==='')
			$path=self::fixPath($this->default_path);
		# Determina el EndPoint (archivo, clase, función y parametros)
		$path_class=$this->searchEndPoint($path);
		# Carga del PHP del EndPoint
		self::loadPHP($this->_php_file);
		# Carga todas las rutas de la clase endpoint
		try{
			$routes=Route::getRoutes($this->main_namespace, $path_class);
		}catch(\ReflectionException $e){
			throw new NotFoundException('Class not available', 0, $e);
		}
		print_r($routes);
		$function='';
		if(count($this->_params))
			$function=strval(array_shift($this->_params));
		$method=Request::getMethod();
		try{
			$route=Route::getRoute($this->main_namespace, $path_class, $function, $method);
		}catch(\ReflectionException $e){
			throw new NotFoundException('Class/Function not available', 0, $e);
		}
		if(is_null($route)){
			throw new NotFoundException('Function not available');
		}
		$route->params=$this->_params;
		$param_missing=$route->min_params-count($route->params);
		if($param_missing>0){
			throw new ParamMissingException($param_missing);
		}
		return $route;
	}
}
