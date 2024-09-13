<?php

namespace MiniRouter;

class Response{
	private static $defConn=false;
	/**
	 * @var callable|null
	 */
	private static $beforeSend;
	/**
	 * @var callable|null
	 */
	private static $afterSend;
	private $http_code=200;
	private $content;
	/**
	 * @var bool|callable
	 */
	private $fixed=false;
	private $headers=[];
	private $incBuffer=false;
	private $conn=0;
	private $sent=false;

	private const CONN_CLOSE=1;
	private const CONN_GZ=2;

	public function __construct(?int $http_code=null, ?string $content_type=null){
		if(!is_null($content_type)) $this->content_type($content_type);
		if(!is_null($http_code)) $this->httpCode($http_code);
	}

	/**
	 * Indica si esta respuesta ya fué enviada
	 * @return bool
	 */
	public function isSent(): bool{
		return $this->sent;
	}

	/**
	 * Intenta la compresón con gz al enviar la respuesta
	 *
	 * Es incompatible con {@see Response::defaultCloseConn()} y {@see Response::closeConn()}
	 * @param bool|null $value
	 * @return bool
	 * @see Response::GZ()
	 */
	public static function defaultGZ(?bool $value=null): bool{
		if($value!==null){
			self::$defConn=$value?self::CONN_GZ:(self::$defConn===self::CONN_GZ?0:self::$defConn);
		}
		return self::$defConn===self::CONN_GZ;
	}

	/**
	 * Intenta el cierre de la conexión al enviar las respuestas
	 *
	 * Es incompatible con {@see Response::defaultGZ()} y {@see Response::GZ()}
	 * @param bool|null $value
	 * @return bool
	 * @see Response::closeConn()
	 */
	public static function defaultCloseConn(?bool $value=null): bool{
		if($value!==null){
			self::$defConn=$value?self::CONN_CLOSE:(self::$defConn===self::CONN_CLOSE?0:self::$defConn);
		}
		return self::$defConn===self::CONN_CLOSE;
	}

	/**
	 * @param bool|null $value
	 * @return bool
	 */
	public static function ignore_user_abort(?bool $value=null): bool{
		return boolval(ignore_user_abort($value));
	}

	/**
	 * @return bool
	 */
	public static function connection_aborted(): bool{
		return boolval(connection_aborted());
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
	 * @return string[]
	 */
	public static function getHeaderList(){
		$list=[];
		foreach(headers_list() as $h){
			[
				$k,
				$v
			]=explode(':', $h, 2);
			$k=mb_convert_case(trim($k), MB_CASE_TITLE);
			$v=trim($v);
			if(!isset($list[$k])){
				$list[$k]=$v;
			}
			else{
				$list[$k].=', '.$v;
			}
		}
		return $list;
	}

	/**
	 * Obtiene la lista de headers de la respuesta
	 * @param string $name
	 * @param bool $as_array
	 * @return array|string|null
	 */
	public static function getHeaderVal(string $name, bool $as_array=false){
		$name=mb_convert_case($name, MB_CASE_TITLE);
		$val=$as_array?[]:null;
		foreach(headers_list() as $h){
			[
				$k,
				$v
			]=explode(':', $h, 2);
			$k=mb_convert_case($k, MB_CASE_TITLE);
			if($k===$name){
				$v=trim($v);
				if(is_array($val)){
					$val[]=$v;
				}
				elseif(is_null($val)){
					$val=$v;
				}
				else{
					$val.=', '.$v;
				}
			}
		}
		if(is_array($val) && count($val)===0) $val=null;
		return $val;
	}

	/**
	 * Elimina todos los niveles del buffer y deja su contenido en un solo nivel para ser utilizado después
	 * @param bool $gz Si es TRUE, habilita la compresión en gz
	 * @return void
	 */
	public static function flatBuffer(bool $gz=false){
		$b=static::getBuffer();
		if($gz){
			ob_start('ob_gzhandler');
		}
		else{
			ob_start();
		}
		echo $b;
	}

	/**
	 * Obtiene el buffer completo de salida, a la vez que elimina todos los niveles del buffer
	 * @return string
	 * @see ob_get_clean()
	 */
	public static function &getBuffer(){
		while(ob_get_level()>1){
			ob_end_flush();
		}
		$b=ob_get_clean();
		if($b===false) $b='';
		return $b;
	}

	/**
	 * Limpia el buffer completo de salida en todos sus niveles. El buffer queda deshabilitado.
	 * @param bool $reset Si es TRUE, vuelve a crear los niveles del buffer vaciós
	 * @return void
	 * @see ob_end_clean()
	 */
	public static function clearBuffer(bool $reset=false){
		if($reset) $l=ob_get_level();
		while(ob_get_level()>0) ob_end_clean();
		if($reset) while(--$l>=0) ob_start();
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
	 * @param string[] $headers
	 * @return bool
	 */
	public static function addHeaders(array $headers, $replace=true){
		foreach($headers as $k=>$v){
			header(trim($k).': '.trim($v), $replace);
		}
		return true;
	}

	/**
	 * El parámetro establece si el buffer se incluirá en la respuesta para el cliente (browser).<br>
	 * Por defecto el buffer está excluido de todas las respuestas
	 * @param bool $include
	 * @return $this
	 */
	public function &includeBuffer($include){
		if($this->isFixed()) return $this;
		$this->incBuffer=boolval($include);
		return $this;
	}

	/**
	 * Habilita/Deshabilita el cierre de la conexión al enviar la respuesta
	 *
	 * Si se habilita, automáticamente se ejecutará {@see Response::ignore_user_abort()} durante el envío, se intentará cerrar la conexión con el cliente para continuar la ejecucion del proceso
	 *
	 * Es incompatible con {@see Response::defaultGZ()} y {@see Response::GZ()}
	 * @param bool $value
	 * @return $this
	 * @see Response::defaultCloseConn()
	 */
	public function &closeConn(bool $value=true): self{
		$this->conn=$value?self::CONN_CLOSE:($this->conn===self::CONN_CLOSE?0:$this->conn);
		return $this;
	}

	/**
	 * Habilita/Deshabilita la compresón con gz al enviar la respuesta
	 *
	 * Es incompatible con {@see Response::defaultCloseConn()} y {@see Response::closeConn()}
	 * @param bool $value
	 * @return $this
	 * @see Response::defaultGZ()
	 */
	public function &GZ(bool $value=true): self{
		$this->conn=$value?self::CONN_GZ:($this->conn===self::CONN_GZ?0:$this->conn);
		return $this;
	}

	protected function flushContent(){
		if($this->isFixed()){
			call_user_func($this->fixed, ...$this->content);
		}
		elseif(is_string($this->content)){
			echo $this->content;
		}
		$this->noContent();
	}

	/**
	 * Obtiene la respuesta completa y desactiva el buffer de salida
	 * @return false|string
	 * @see Response::send()
	 */
	public function getContent(){
		if(!$this->isIncludeBuffer()) static::clearBuffer();
		ob_start();
		$this->flushContent();
		return static::getBuffer();
	}

	/**
	 * Registra/reemplaza la función que se ejecuta automáticamente antes de enviar una respuesta (al invocar {@see Response::send()}).
	 *
	 * Solo puede haber una función registrada, y aplica para cualquier objeto de tipo {@see Response} o cualquier otra clase que herede de esta.
	 * @param callable|null $func Esta función solo recibe un parámetro: el objeto {@see Response} que se va a enviar
	 * @return void
	 * @throws \Exception
	 */
	public static function beforeSend(?callable $func){
		if(!is_null($func)){
			try{
				$rfn=new \ReflectionFunction($func);
				if($rfn->getNumberOfRequiredParameters()>1) throw new \Exception('Too many params', 0);
				if(($param=$rfn->getParameters()[0] ?? null) && ($type=$param->getType())){
					if(is_a($type, \ReflectionNamedType::class)) $types=[$type->getName()];
					elseif(is_a($type, \ReflectionUnionType::class) || is_a($type, \ReflectionIntersectionType::class)) $types=array_map('strval', $type->getTypes());
					else throw new \Exception('Unable to read param type', 0);
					if(!in_array(self::class, $types)) throw new \Exception('Invalid param type', 0);
				}
			}catch(\ReflectionException $e){
				throw new \Exception('Invalid function', 0, $e);
			}
		}
		self::$beforeSend=$func;
	}

	/**
	 * Registra/reemplaza la función que se ejecuta automáticamente después de enviar una respuesta (al invocar {@see Response::send()}).
	 *
	 * Solo puede haber una función registrada, y aplica para cualquier objeto de tipo {@see Response} o cualquier otra clase que herede de esta.
	 * @param callable|null $func Esta función no recibe un parámetro: el objeto {@see Response} que se va a enviar
	 * @return void
	 * @throws \Exception
	 */
	public static function afterSend(?callable $func){
		if(!is_null($func)){
			try{
				$rfn=new \ReflectionFunction($func);
				if($rfn->getNumberOfRequiredParameters()>1) throw new \Exception('Too many params', 0);
				if(($param=$rfn->getParameters()[0] ?? null) && ($type=$param->getType())){
					if(is_a($type, \ReflectionNamedType::class)) $types=[$type->getName()];
					elseif(is_a($type, \ReflectionUnionType::class) || is_a($type, \ReflectionIntersectionType::class)) $types=array_map('strval', $type->getTypes());
					else throw new \Exception('Unable to read param type', 0);
					if(!in_array(self::class, $types)) throw new \Exception('Invalid param type', 0);
				}
			}catch(\ReflectionException $e){
				throw new \Exception('Invalid function', 0, $e);
			}
		}
		self::$afterSend=$func;
	}

	/**
	 * @return callable|null
	 */
	public static function getBeforeSend(): ?callable{
		return self::$beforeSend;
	}

	/**
	 * @return callable|null
	 */
	public static function getAfterSend(): ?callable{
		return self::$afterSend;
	}

	protected function triggerBeforeSend(){
		if(is_callable(self::$beforeSend)){
			call_user_func(self::$beforeSend, $this);
		}
	}

	protected function triggerAfterSend(){
		if(is_callable(self::$afterSend)){
			call_user_func(self::$afterSend, $this);
		}
	}

	/**
	 * Establece las configuraciones y headers (basado en el buffer) para intentar cerra la conexión al enviar la respuesta
	 *
	 * @return void
	 * @see Response::closeConn()
	 */
	private static function fixCloseConn(){
		$len=ob_get_length();
		static::ignore_user_abort(true);
		static::addHeaders([
			'Content-Encoding'=>'none',
			'Content-Length'=>$len,
			'Connection'=>'close',
		]);
		//Deshabilita la compresión de apache
		if(function_exists('apache_setenv')) apache_setenv('no-gzip', '1');
		if(preg_match('/nginx/i', $_SERVER['SERVER_SOFTWARE'] ?? '')){
			//Deshabilita la compresión de nginx
			header('X-Accel-Buffering: no');
		}
		if(($min=ini_get('output_buffering'))!==false){
			$diff=intval($min)-$len;
			//Completa el tamaño minimo de salida del buffer para el envío de la respuesta
			if($diff>0) echo str_repeat("\0", $diff)."\n";
		}
	}

	/**
	 * Intenta cerra la conexión luego del envío de la respuesta
	 *
	 * @return void
	 * @see Response::closeConn()
	 */
	private static function fixAfterClose(){
		if(function_exists('fastcgi_finish_request')) fastcgi_finish_request();
	}

	/**
	 * Envía la respuesta completa al cliente y desactiva el buffer de salida
	 * @return bool
	 */
	public function send(){
		$cli=Request::isCLI();
		if($this->isSent()) return false;
		if(!$cli && static::headers_sent()) return false;
		$this->sent=true;
		$this->triggerBeforeSend();
		if($cli){
			if(!$this->isIncludeBuffer()) static::clearBuffer();
			$this->flushContent();
			static::flushBuffer();
			$this->triggerAfterSend();
			return true;
		}
		static::addHeaders($this->getHeaders());
		if(!$this->isIncludeBuffer()) static::clearBuffer();
		http_response_code($this->getHttpCode());
		$close=$this->isCloseConn() || (!$this->isFixed() && static::defaultCloseConn());
		$gz=$close?false:($this->isGz() || (!$this->isFixed() && static::defaultGZ()));
		static::flatBuffer($gz);
		$this->flushContent();
		if($close){
			self::fixCloseConn();
		}
		static::flushBuffer();
		if($close) self::fixAfterClose();
		$this->triggerAfterSend();
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
		return $this->conn===self::CONN_CLOSE;
	}

	/**
	 * @return bool
	 */
	public function isGz(): bool{
		return $this->conn===self::CONN_GZ;
	}

	/**
	 * @return bool
	 */
	public function isIncludeBuffer(): bool{
		return $this->incBuffer;
	}

	public function hasContent(){
		return !is_null($this->content);
	}

	public function &noContent(){
		$this->content=null;
		$this->fixed=false;
		return $this;
	}

	public function &content(string $content){
		$this->content=$content;
		$this->fixed=false;
		return $this;
	}

	public function &fixContent(callable $fixedFunc, ...$content){
		$this->content=$content;
		$this->fixed=$fixedFunc;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isFixed(): bool{
		return !!$this->fixed;
	}

	/**
	 * Agrega, reemplaza y elimina varios headers de la respuesta.<br>
	 * Si el vlaor es NULL, el header se elimina de la lista
	 * @param array $headers Lista de headers. Cada par ("key"=>"value") de la lista se enviará como el header "key: value"
	 * @return $this
	 */
	public function &headers(array $headers){
		foreach($headers as $name=>$value){
			$this->header($name, $value);
		}
		return $this;
	}

	/**
	 * @return array
	 */
	public function getHeaders(){
		return $this->headers;
	}

	public function getHeader(string $name){
		return $this->headers[mb_convert_case(trim($name), MB_CASE_TITLE)] ?? null;
	}

	public function &header(string $name, ?string $value){
		if(strpos($name, ':')>0){
			list($name, $value)=explode(':', $name, 2);
		}
		if(is_null($value)) unset($this->headers[mb_convert_case(trim($name), MB_CASE_TITLE)]);
		else $this->headers[mb_convert_case(trim($name), MB_CASE_TITLE)]=strval($value);
		return $this;
	}

	public function &download(string $name='download.tmp'){
		return $this->header('Content-Disposition', 'attachment; filename='.$name);
	}

	public function &noDownload(){
		return $this->header('Content-Disposition', null);
	}

	public function get_content_type(){
		return $this->getHeader('Content-Type');
	}

	public function &content_type(?string $content_type){
		return $this->header('Content-Type', $content_type);
	}

	public function __clone(){
		$this->sent=false;
	}

	public static function &r_file(string $filename, $mime=null, ?string $download=null){
		$new=null;
		if(is_file($filename)){
			$new=new static(null, $mime ?? 'application/octet-stream');
			if(is_string($download)) $new->download($download);
			$new->fixContent('readfile', $filename)->closeConn();
		}
		return $new;
	}

	/**
	 * @param $resource
	 * @param $mime
	 * @param string|null $download
	 * @param null|string $close_gz null|"close"|"gz"
	 * @return static|null
	 */
	public static function &r_resource($resource, $mime=null, ?string $download=null){
		$new=null;
		if(is_resource($resource)){
			$new=new static(null, $mime ?? 'application/octet-stream');
			if(is_string($download)) $new->download($download);
			$new->fixContent('fpassthru', $resource);
		}
		return $new;
	}

	public static function &r_json($data){
		return (new static(null, 'application/json'))->content(json_encode($data));
	}

	public static function &r_text(string $text, $buffer=false){
		return (new static(null, 'text/plain'))->content($text)->includeBuffer($buffer);
	}

	public static function &r_html(string $html, $buffer=false){
		return (new static(null, 'text/html'))->content($html)->includeBuffer($buffer);
	}

	public static function &r_redirect(string $location){
		return (new static(302))->header('location', $location);
	}

	public static function r_empty(){
		return new static(204);
	}

	public static function r_forbidden(){
		return new static(403);
	}

}
