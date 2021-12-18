<?php
namespace MiniRouter;

class Response{
	protected static $default_content_type='text/plain';

	protected $http_code=200;
	protected $content_type;
	protected $content='';
	protected $extraHeaders=[];
	protected $includeBuffer=false;
	protected $nocache=true;

	public function __construct($content_type=null){
		$this->set_content_type(is_string($content_type)?$content_type:static::$default_content_type);
	}

	/**
	 * @param null|bool $value
	 * @return bool
	 */
	public static function continue_on_disconnect($value=null){
		return ignore_user_abort($value);
	}

	public static function default_content_type($content_type=null){
		if(!is_null($content_type)) static::$default_content_type=strval($content_type);
		return static::$default_content_type;
	}

	public static function headers_sent(&$file=null, &$line=null){
		return headers_sent($file, $line);
	}

	public static function getHeaderList(){
		$list=[];
		foreach(headers_list() as $h){
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
		static::flatBuffer();
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
		foreach($headers as $k=>$v){
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

	public function send($includeBuffer=false){
		if(static::headers_sent()) return false;
		if(!$includeBuffer) static::clearBuffer();
		ob_start();
		echo $this->content;
		static::flatBuffer();
		static::addHeaders($this->extraHeaders);
		header('Content-Type: '.$this->content_type);
		header('Content-Length: '.ob_get_length());
		header('Connection: close', true, $this->http_code);
		http_response_code($this->http_code);
		static::sendBuffer();
		return true;
	}

	public function send_exit($includeBuffer=false){
		$this->send($includeBuffer);
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

	function &cache(int $max_age){
		if($max_age>0){
			$this->extraHeaders['Cache-Control']='max-age='.$max_age;
		}
		else{
			$this->extraHeaders['Cache-Control']='no-store, no-cache, must-revalidate, max-age=0';
			$this->extraHeaders['Pragma']='no-cache';
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

	static function &json($data): self{
		return (new static('application/json'))->content(json_encode($data));
	}

	static function &text($text): self{
		return (new static('text/plain'))->content($text);
	}

	static function &html($html): self{
		return (new static('text/html'))->content($html);
	}

	static function &redirect($location): self{
		return (new static())->headers(['location'=>$location])->http_code(302);
	}
}
