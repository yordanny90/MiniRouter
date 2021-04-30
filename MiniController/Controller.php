<?php
namespace MiniCtrl;
if(!class_exists('MiniCtrl\\Controller')){
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
	 * Class Controller
	 * @package MiniCtrl
	 * @see Controller::_init()
	 */
	abstract class Controller{
		/**
		 * Si se define este valor, siempre se ejecutará una misma función. El nombre de esta función se verá afectada por ({@see MiniController::$_enable_RESTful})
		 * @var null|string
		 */
		protected static $_unique_fn=null;
		/**
		 * Este es el nombre de la función que se ejecuta si el nombre no se puede obtener de la dirección
		 * @var string
		 */
		protected static $_default_fn='index';
		/**
		 * Si es <b>TRUE</b>, las funciones que se ejecutan a través de un request, deben tener el método del request como prefijo.<br>
		 * Ejemplo, al llamar la URL "/miclase/mifn" por el método GET, se ejecutaría la función llamada "GET_mifn", así como el método PUT de esa misma URL ejecutaría la función "PUT_mifn".<br>
		 * Si es <b>FALSE</b>, las funciones no tendrían el método como prefijo, por lo que, sin importar el método que se use, la función a ejecutar por la URL "/miclase/mifn" siempre sería "mifn"
		 * @var bool Default: TRUE. Define si es un controlador RESTful o no.
		 * @see MiniController::$method_accepted
		 */
		protected static $_enable_RESTful=true;
		/**
		 * Si en el controlador está habilitado el RESTful ({@see MiniController::$_enable_RESTful}), esta es la lista de métodos aceptados en la API
		 * @var array
		 */
		protected static $_method_accepted=array('GET','POST','PUT','DELETE');

		/**
		 * MiniController constructor.
		 * @throws MiniCtrlError
		 */
		public function __construct(){
			if(Controller::_getInstance()){
				throw new MiniCtrlError('Ya existe un controlador en proceso');
			}
		}

		private static $_instance=null;

		/**
		 * Devuelve en controlador que está actualmente en proceso
		 * @return Controller|null
		 */
		final public static function &_getInstance(){
			return Controller::$_instance;
		}

		/**
		 * @param string $path
		 * @param string $default_controller
		 * @param string $c_suffix
		 * @throws MiniCtrlError
		 * @throws \ErrorException
		 */
		public static function _init($path, $default_controller='index', $c_suffix='.ctrl.php'){
			if(Controller::_getInstance()){
				throw new MiniCtrlError('Ya existe un controlador en proceso');
			}
			if(headers_sent($file,$line)){
				throw new \ErrorException('Los headers ya fueron enviados: '.$file.' ['.$line.']');
			}
			unset($file,$line);
			/* Los headers permiten acceso desde otro dominio (CORS) a nuestro REST API o desde un cliente remoto via HTTP
			 * Removiendo las lineas header() limitamos el acceso a nuestro RESTfull API a el mismo dominio
			 * Nótese los métodos permitidos en Access-Control-Allow-Methods. Esto nos permite limitar los métodos de consulta a nuestro RESTfull API
			 * Mas información: https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS
			 **/
			header("Access-Control-Allow-Origin: *");
			header('Access-Control-Allow-Credentials: true');
			//header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');
			header("Access-Control-Allow-Headers: X-Requested-With");
			header('Content-Type: text/html; charset=utf-8');
			header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');
			$c_path=realpath($path);
			if(!$c_path){
				throw new MiniCtrlError('Directorio de controladores no encontrado');
			}
			$c_path.='/';
			$PATH_INFO=trim(isset($_SERVER['PATH_INFO'])?substr($_SERVER['PATH_INFO'],1):'');
			if(!$PATH_INFO){
				$PATH_INFO=$default_controller;
			}
			if($PATH_INFO==''){
				throw (new MiniCtrlError('Nombre del controlador no recibido'))->typeNotFound();
			}
			$c_params=explode('/', $PATH_INFO);
			unset($PATH_INFO);
			$c_name_parts=array();
			do{
				$c_class=array_shift($c_params);
				$c_name_parts[]=$c_class;
				$c_dir=$c_path.implode('/', $c_name_parts);
				$c_file=$c_dir.$c_suffix;
			}while(count($c_params)>0 && is_dir($c_dir) && !is_file($c_file));
			define('MINICTRL_ENDPOINT',implode('/',$c_name_parts));
            define('MINICTRL_PARAMS',implode('/',$c_params));
			unset($c_name_parts);
			unset($c_dir);
			if(!is_file($c_file)){
				throw (new MiniCtrlError('Controlador no encontrado'))->typeNotFound();
			}
			include($c_file);
			unset($c_file);
			if(__NAMESPACE__){
				$c_class=__NAMESPACE__.'\\'.$c_class;
			}
			if(!class_exists($c_class)){
				throw (new MiniCtrlError('Controlador no válido'))->typeNotFound();
			}
			if(!in_array(Controller::class, class_parents($c_class))){
				throw (new MiniCtrlError('La clase encontrada no es un controlador'))->typeNotFound();
			}

			// Validación de la función
			$c_vars=get_class_vars($c_class);
			if(is_array($c_vars['_method_accepted']) && count($c_vars['_method_accepted'])){
				header('Access-Control-Allow-Methods: '.implode(', ', $c_vars['_method_accepted']));
				if(!in_array(Request::getMethod(),$c_vars['_method_accepted'])){
					throw (new MiniCtrlError('Method no aceptado por el controlador'))->typeMethodNotAllowed();
				}
			}
			$c_fn=null;
			if($c_vars['_unique_fn']){
				$c_fn=$c_vars['_unique_fn'];
			}
			if(!$c_fn){
				$c_fn=array_shift($c_params);
			}
			if(!$c_fn && $c_vars['_default_fn']){
				$c_fn=$c_vars['_default_fn'];
			}
			if(substr($c_fn, 0,1)=='_'){
				throw (new MiniCtrlError('EndPoint de la API no encontrado'))->typeNotFound();
			}
			if($c_fn && $c_vars['_enable_RESTful'] && Request::getMethod()){
				$c_fn=Request::getMethod().'_'.$c_fn;
			}
			unset($c_vars);
			$fn_validator=null;
			try{
				$fn_validator=new \ReflectionMethod($c_class, $c_fn);
			}catch(\ReflectionException $e){
			}
			if(!$fn_validator){
				throw (new MiniCtrlError('EndPoint de la API no encontrado'))->typeNotFound();
			}
			if(!$fn_validator->isPublic()){
				throw (new MiniCtrlError('EndPoint de la API no encontrado'))->typeNotFound();
			}
			if($fn_validator->isStatic()){
				throw (new MiniCtrlError('EndPoint de la API no encontrado'))->typeNotFound();
			}
			unset($fn_validator);

			// Ejecución de la función
			$result=null;
			Controller::$_instance=new $c_class();
			call_user_func_array(array(Controller::$_instance, $c_fn),$c_params);
			// Se destruye el controlador
			Controller::$_instance=null;
		}
	}
}
