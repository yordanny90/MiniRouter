<?php

namespace MiniRouter;

class Response{
	private static $tryGZ=false;
	private static $tryCloseConn=false;
	private $http_code=200;
	private $content;
	private $extraHeaders=[];
	private $include_buffer=false;
	private $close_conn=false;
	private $gz=false;

	public function __construct($content_type=null){
		$this->content_type($content_type);
	}

	/**
	 * Intentar comprimir todos los response antes de enviarlos
	 *
	 * Es incompatible con {@see Response::tryGlobalCloseConn()} y {@see Response::closeConn()}
	 * @param bool $tryGZ
	 * @see Response::GZ()
	 */
	public static function tryGlobalGZ(bool $tryGZ): void{
		static::$tryGZ=$tryGZ;
		if(static::$tryGZ) static::tryGlobalCloseConn(false);
	}

	/**
	 * @return bool
	 */
	public static function isTryGlobalGZ(): bool{
		return static::$tryGZ;
	}

	/**
	 * Intentar comprimir todos los response antes de enviarlos
	 *
	 * Es incompatible con {@see Response::tryGlobalGZ()} y {@see Response::GZ()}
	 * @param bool $tryCloseConn
	 * @see Response::closeConn()
	 */
	public static function tryGlobalCloseConn(bool $tryCloseConn): void{
		static::$tryCloseConn=$tryCloseConn;
		if(static::$tryCloseConn) static::tryGlobalGZ(false);
	}

	/**
	 * @return bool
	 */
	public static function isTryGlobalCloseConn(): bool{
		return static::$tryCloseConn;
	}

	/**
	 * @param null|bool $value
	 * @return bool
	 */
	public static function continue_on_disconnect($value=null){
		return ignore_user_abort($value);
	}

	/**
	 * @return bool
	 */
	public static function connection_aborted(){
		return connection_aborted();
	}

	/**
	 * Valida si los headers ya se enviaron
	 * @return false|string
	 */
	public static function headers_sent(){
		return headers_sent($file, $line)?$file.':'.$line:false;
	}

	/**
	 * Obtiene la lista de headers de la respuesta
	 * @return array
	 */
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
	 * @param bool $gz Si es TRUE, habilita la compresión en gz
	 * @return void
	 */
	public static function flatBuffer(bool $gz=false){
		$b=static::getBuffer();
		if($gz) ob_start('ob_gzhandler');
		else ob_start();
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
		$this->include_buffer=boolval($include);
		return $this;
	}

	/**
	 * Habilita/Dehabilita el cierre de la conexión al enviar la respuesta
	 *
	 * Es incompatible con {@see Response::tryGlobalGZ()} y {@see Response::GZ()}
	 * @param bool $val
	 * @return $this
	 * @see Response::tryGlobalCloseConn()
	 */
	public function &closeConn($val){
		$this->close_conn=boolval($val);
		if($this->close_conn) $this->GZ(false);
		return $this;
	}

	/**
	 * Habilita/Dehabilita la compresón con gz
	 *
	 * Es incompatible con {@see Response::tryGlobalCloseConn()} y {@see Response::closeConn()}
	 * @param bool $val
	 * @return $this
	 * @see Response::tryGlobalGZ()
	 */
	public function &GZ($val){
		$this->gz=boolval($val);
		if($this->gz) $this->closeConn(false);
		return $this;
	}

	protected function flushContent(){
		if(is_string($this->content)){
			echo $this->content;
		}
		elseif(is_resource($this->content ?? null)){
			fpassthru($this->content);
			fclose($this->content);
		}
		$this->content=null;
	}

	private function mergeContent(bool $tryGZ=false){
		if(!$this->include_buffer) static::clearBuffer();
		static::flatBuffer($this->isGz() || ($tryGZ && !$this->isCloseConn()));
		$this->flushContent();
	}

	/**
	 * Obtiene la respuesta completa y desactiva el buffer de salida
	 * @return false|string
	 * @see Response::send()
	 */
	public function getContent(){
		$this->mergeContent();
		return static::getBuffer();
	}

	/**
	 * Envía la respuesta completa al cliente y desactiva el buffer de salida
	 * @return bool
	 */
	public function send(){
		if(static::headers_sent()){
			return false;
		}
		static::addHeaders($this->extraHeaders);
		$this->mergeContent(static::isTryGlobalGZ());
		http_response_code($this->http_code);
		if($this->isCloseConn() || (!$this->isGz() && static::isTryGlobalCloseConn())){
			$length=ob_get_length();
			header('Content-Length: '.$length, true);
			header('Connection: close', true);
			if($length==0) echo "\0";
		}
		static::flushBuffer();
		return true;
	}

	public function &httpCode($http_code){
		if(is_int($http_code) && $http_code>0){
			$this->http_code=$http_code;
		}
		return $this;
	}

	/**
	 * @return int
	 */
	public function getHttpCode(): int{
		return $this->http_code;
	}

	/**
	 * @return bool
	 */
	public function isCloseConn(): bool{
		return $this->close_conn;
	}

	/**
	 * @return bool
	 */
	public function isGz(): bool{
		return $this->gz;
	}

	/**
	 * @return bool
	 */
	public function isIncludeBuffer(): bool{
		return $this->include_buffer;
	}

	public function &download(string $name='download.tmp'){
		$this->extraHeaders['Content-Disposition']='attachment; filename='.$name;
		return $this;
	}

	public function &noDownload(){
		unset($this->extraHeaders['Content-Disposition']);
		return $this;
	}

	public function hasContent(){
		return !is_null($this->content);
	}

	public function &noContent(){
		$this->content=null;
		return $this;
	}

	public function &content(string $content){
		$this->content=$content;
		return $this;
	}

	public function &contentFile(string $filename, $context=null){
		$res=fopen($filename, 'r', false, $context);
		if(is_resource($res)){
			$this->content=$res;
		}
		$this->noContent();
		return $this;
	}

	/**
	 * @param null|resource $res
	 * @return $this
	 */
	public function &contentResource($res){
		if(is_resource($res)){
			$this->content=$res;
		}
		$this->noContent();
		return $this;
	}

	/**
	 * Agrega, reemplaza y elimina varios headers de la respuesta.<br>
	 * Si el vlaor es NULL, el header se elimina de la lista
	 * @param array $headers Lista de headers. Cada par ("key"=>"value") de la lista se enviará como el header "key: value"
	 * @return $this
	 */
	public function &headers(array $headers){
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
		return $this->extraHeaders['Content-Type'] ?? null;
	}

	public function &content_type(?string $content_type){
		if(is_string($content_type)) $this->extraHeaders['Content-Type']=$content_type;
		else unset($this->extraHeaders['Content-Type']);
		return $this;
	}

	public static function &r_file($filename, $context=null, $mime=null): self{
		return (new static($mime ?? 'application/octet-stream'))->contentFile($filename, $context);
	}

	public static function &r_resource($resource, $mime=null): self{
		return (new static($mime ?? 'application/octet-stream'))->contentResource($resource);
	}

	public static function &r_json($data): self{
		return (new static('application/json'))->content(json_encode($data));
	}

	public static function &r_text(string $text): self{
		return (new static('text/plain'))->content($text);
	}

	public static function &r_html(string $html): self{
		return (new static('text/html'))->content($html);
	}

	public static function &r_redirect(string $location): self{
		return (new static())->headers(['location'=>$location])->httpCode(302);
	}

	public static function &r_empty(): self{
		return (new static())->httpCode(204);
	}

}
