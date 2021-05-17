<?php

namespace MiniRouter;
class Response{
	protected static $default_content_type='text/plain';

	protected $http_code=200;
	protected $content_type;
	protected $content='';
	protected $extraHeaders=[];
	protected $includeBuffer=false;

	public function __construct($content_type=null){
		$this->set_content_type(is_string($content_type)?$content_type:self::$default_content_type);
	}

	/**
	 * @param null|bool $value
	 * @return bool
	 */
	public static function continue_on_disconnect($value=null){
		return ignore_user_abort($value);
	}

	public static function default_content_type($content_type=null){
		if(!is_null($content_type)) self::$default_content_type=strval($content_type);
		return self::$default_content_type;
	}

	public static function headers_sent(&$file=null, &$line=null){
		return headers_sent($file, $line);
	}

	public static function getHeaderList(){
		$list=[];
		foreach(headers_list() AS $h){
			$h=explode(':', $h, 2);
			$h[0]=mb_convert_case($h[0], MB_CASE_TITLE);
			$list[$h[0]]=trim($h[1]);
		}
		return $list;
	}

	/**
	 * Elimina todos los niveles del buffer y deja su contenido en un solo nivel para ser utilizado después
	 */
	public static function flatBuffer(){
		while(ob_get_level()>1){
			ob_get_flush();
		}
		if(!ob_get_level()) ob_start();
	}

	/**
	 * Obtiene el buffer completo de salida, a la vez que elimina todos los niveles del buffer
	 * @return string
	 * @see Response::flatBuffer()
	 * @see ob_get_clean()
	 */
	public static function getBuffer(){
		self::flatBuffer();
		return ob_get_clean();
	}

	/**
	 * Limpia el buffer completo de salida en todos sus niveles. El buffer queda deshabilitado.
	 * @see ob_end_clean()
	 */
	public static function clearBuffer(){
		while(ob_get_level()) ob_end_clean();
	}

	/**
	 * Envia todos los niveles del buffer de salida al cliente (browser). No almacena el buffer en una variable como en {@see getBuffer()}
	 * @see ob_end_flush()
	 * @see flush()
	 */
	public static function sendBuffer(){
		while(ob_get_level()) ob_end_flush();
		flush();
	}

	/**
	 * Agrega los headers para enviarlos al cliente (browser).<br>
	 * Se enviarán cuando el proceso finalize o cuando se envíe una respuesta
	 * @param array $headers
	 */
	public static function addHeaders(array $headers){
		foreach($headers AS $k=>$v){
			header($k.': '.$v);
		}
	}

	/**
	 * El parámetro establece si el buffer se incluirá en la respuesta para el cliente (browser).<br>
	 * Por defecto el buffer está excluido de todas las respuestas
	 * @param null|bool $include
	 * @return bool
	 */
	public function includeBuffer($include=null){
		if(!is_null($include)) $this->includeBuffer=boolval($include);
		return $this->includeBuffer;
	}

	public function send(){
		if(self::headers_sent()) return false;
		if(!$this->includeBuffer) self::clearBuffer();
		ob_start();
		echo $this->content;
		self::flatBuffer();
		self::addHeaders($this->extraHeaders);
		self::addHeaders(['Content-Length'=>ob_get_length()]);
		header('Content-Type: '.$this->content_type, true, $this->http_code);
		self::sendBuffer();
		return true;
	}

	public function send_exit(){
		$this->send();
		exit;
	}

	function &http_code($http_code){
		if(is_int($http_code) && $http_code>0){
			$this->http_code=$http_code;
		}
		return $this;
	}

	function &content($content){
		if(is_string($content)) $this->content=$content;
		return $this;
	}

	/**
	 * Agrega, reemplaza y elimina varios headers de la respuesta.<br>
	 * Si el vlaor es NULL, el header se elimina de la lista
	 * @param array $headers Lista de headers. Cada par ("key"=>"value") de la lista se enviará como el header "key: value"
	 * @return $this
	 */
	function &headers(array $headers){
		foreach($headers as $name=>$value){
			if(is_null($value)) unset($this->extraHeaders[mb_convert_case(trim($name), MB_CASE_TITLE)]);
			else $this->extraHeaders[mb_convert_case(trim($name), MB_CASE_TITLE)]=strval($value);
		}
		return $this;
	}

	/**
	 * @return array
	 */
	public function getHeaders(){
		return $this->extraHeaders;
	}

	public function get_content_type(){
		return $this->content_type;
	}

	public function set_content_type($content_type){
		$this->content_type=strval($content_type);
	}

	/**
	 * @param $data
	 * @return Response
	 */
	static function &json($data){
		return (new self('application/json'))->content(json_encode($data));
	}

	static function &json_string($json){
		return (new self('application/json'))->content($json);
	}

	static function &text($text){
		return (new self('text/plain'))->content($text);
	}

	static function &html($html){
		return (new self('text/html'))->content($html);
	}

	static function &xml(\SimpleXMLElement $data){
		return (new self('application/xml'))->content($data->saveXML());
	}

	static function &xml_string($xml){
		return (new self('application/xml'))->content($xml);
	}
}
