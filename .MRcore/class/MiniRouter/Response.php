<?php

namespace MiniRouter;

class Response{
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
		$b=self::getBuffer();
		ob_start();
		echo $b;
	}

	/**
	 * Obtiene el buffer completo de salida, a la vez que elimina todos los niveles del buffer
	 * @return string
	 * @see ob_get_clean()
	 */
	public static function &getBuffer(){
		$b='';
		while(ob_get_level()>0){
			$b=ob_get_clean().$b;
		}
		return $b;
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

	public function send(){
		if(static::headers_sent()) return false;
		if(!$this->includeBuffer) static::clearBuffer();
		static::flatBuffer();
		$this->flushContent();
		static::addHeaders($this->extraHeaders);
		if($this->closeConn){
			header('Content-Length: '.ob_get_length(), true);
			header('Connection: close', true);
		}
		http_response_code($this->http_code);
		static::flushBuffer();
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

	private function flushContent(){
		if(is_string($this->content)){
			echo $this->content;
		}
		elseif(is_resource($this->content ?? null)){
			fpassthru($this->content);
			fclose($this->content);
		}
		$this->content=null;
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
		$this->http_code(200);
		return $this;
	}

	function &contentFile(string $filename, $context=null){
		$res=fopen($filename, 'r', false, $context);
		if(is_resource($res)){
			$this->content=$res;
			$this->http_code(200);
		}
		$this->noContent();
		$this->http_code(204);
		return $this;
	}

	/**
	 * @param null|resource $res
	 * @return $this
	 */
	function &contentResource($res){
		if(is_resource($res)){
			$this->content=$res;
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

	static function &r_file($filename, $context=null, $mime=null): self{
		return (new static($mime ?? 'application/octet-stream'))->contentFile($filename, $context);
	}

	static function &r_resource($resource, $mime=null): self{
		return (new static($mime ?? 'application/octet-stream'))->contentResource($resource);
	}

	static function &r_json($data): self{
		return (new static('application/json'))->content(json_encode($data));
	}

	static function &r_text(string $text): self{
		return (new static('text/plain'))->content($text);
	}

	static function &r_html(string $html): self{
		return (new static('text/html'))->content($html);
	}

	static function &r_redirect(string $location): self{
		return (new static())->headers(['location'=>$location])->http_code(302)->noCache();
	}

	static function &r_empty(): self{
		return (new static())->noContent();
	}

}
