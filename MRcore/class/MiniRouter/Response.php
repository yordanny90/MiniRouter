<?php

namespace MiniRouter;

class Response{
	protected static $default_content_type='text/plain';

	protected $http_code=200;
	protected $content;
	protected $extraHeaders=[];
	protected $includeBuffer=false;
	protected $closeConn=true;

	public function __construct($content_type=null){
		$this->content_type($content_type);
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
			ob_end_flush();
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
		while(ob_get_level()>0) ob_end_clean();
	}

	/**
	 * Envia todos los niveles del buffer de salida al cliente (browser). No almacena el buffer en una variable como en {@see getBuffer()}
	 * @see ob_end_flush()
	 * @see flush()
	 */
	public static function flushBuffer(){
		while(ob_get_level()>0) ob_end_flush();
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
	 * @param bool $include
	 * @return $this
	 */
	public function &includeBuffer($include){
		$this->includeBuffer=boolval($include);
		return $this;
	}

	/**
	 * @param bool $val
	 * @return $this
	 */
	public function &closeConn($val){
		$this->closeConn=boolval($val);
		return $this;
	}

	public function content_size(){
		if(is_string($this->content)){
			return strlen($this->content);
		}
		elseif(is_object($this->content) && is_string($this->content->file ?? null) && is_int($this->content->size ?? null)){
			return $this->content->size;
		}
		return 0;
	}

	private function flushContent(){
		if(is_string($this->content)){
			echo $this->content;
			flush();
		}
		elseif(is_array($this->content) && is_string($this->content->file ?? null) && is_int($this->content->size ?? null)){
			if(!($this->content->res ?? null)){
				$this->content->res=fopen($this->content->file ?? null, 'r', false, $this->content->context ?? null);
			}
			if($this->content->res){
				fpassthru($this->content->res);
				flush();
				fclose($this->content->res);
			}
		}
	}

	public function send(){
		if(static::headers_sent()) return false;
		if($this->closeConn) $length=$this->content_size();
		if($this->includeBuffer){
			static::flatBuffer();
			if($this->closeConn) $length+=ob_get_length();
		}
		else{
			static::clearBuffer();
		}
		static::addHeaders($this->extraHeaders);
		if($this->closeConn){
			header('Content-Length: '.$length);
			header('Connection: close', true, $this->http_code);
		}
		http_response_code($this->http_code);
		static::flushBuffer();
		$this->flushContent();
		return true;
	}

	function &http_code($http_code){
		if(is_int($http_code) && $http_code>0){
			$this->http_code=$http_code;
		}
		return $this;
	}

	function &download(string $name='download.tmp'){
		$this->extraHeaders['Content-Disposition']='attachment; filename='.$name;
		return $this;
	}

	function &noDownload(){
		unset($this->extraHeaders['Content-Disposition']);
		return $this;
	}

	function hasContent(){
		return !is_null($this->content);
	}

	function &noContent(){
		$this->content=null;
		$this->http_code(204);
		return $this;
	}

	function &content(string $content){
		$this->content=$content;
		return $this;
	}

	function &contentFile(string $filename, $context=null){
		$res=fopen($filename, 'r', false, $context);
		if($res){
			$this->content=(object)[
				'res'=>$res,
				'size'=>fstat($res)['size'],
				'file'=>$filename,
				'context'=>$context,
			];
			$this->http_code(200);
		}
		$this->noContent();
		$this->http_code(204);
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

	function &noCache(){
		return $this->cache(0);
	}

	/**
	 * @param int $max_age Para desactivar la cache se establece en cero (0)
	 * @return $this
	 */
	function &cache(int $max_age){
		if($max_age>0){
			$this->extraHeaders['Cache-Control']='max-age='.$max_age;
			unset($this->extraHeaders['Pragma']);
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
		return $this->extraHeaders['Content-Type'] ?? null;
	}

	public function &content_type(?string $content_type){
		if(is_string($content_type)) $this->extraHeaders['Content-Type']=$content_type;
		else unset($this->extraHeaders['Content-Type']);
		return $this;
	}

	static function &file($filename, $context=null, $mime=null): self{
		return (new static($mime ?? 'application/octet-stream'))->download(basename($filename))->contentFile($filename, $context);
	}

	static function &json($data): self{
		return (new static('application/json'))->content(json_encode($data));
	}

	static function &text(string $text): self{
		return (new static('text/plain'))->content($text);
	}

	static function &html(string $html): self{
		return (new static('text/html'))->content($html);
	}

	static function &redirect(string $location): self{
		return (new static())->headers(['location'=>$location])->http_code(302)->cache(0);
	}

	static function &empty(): self{
		return (new static())->noContent();
	}

}
