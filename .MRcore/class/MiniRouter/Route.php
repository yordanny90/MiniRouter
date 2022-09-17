<?php
namespace MiniRouter;

/**
 * Class Route
 * @package MiniRouter
 * @property array $exec_params
 */
class Route{
	/**
	 * @var string Ruta válida del Endpoint
	 */
	protected $path;
	/**
	 * @var array Lista de métodos permitidos para esta ruta
	 */
	protected $allows=[];
	/**
	 * @var string Método de esta ruta
	 */
	protected $method;
	/**
	 * @var \ReflectionMethod
	 */
	protected $ref;
	/**
	 * @var bool Indica si la ejecución está en curso.
	 *
	 * Una vez terminada, vuelve a ser false
	 */
	protected $started=false;

	protected function __construct(\ReflectionMethod $ref){
		$this->ref=$ref;
	}

	/**
	 * @return bool
	 */
	public function isStarted(){
		return $this->started;
	}

	/**
	 * @param array $allows
	 */
	public function setAllows(array $allows){
		$this->allows=array_unique($allows);
	}

	/**
	 * @return array
	 */
	public function getAllows(){
		return $this->allows;
	}

	/**
	 * @return string
	 */
	public function getPath(): string{
		return $this->path;
	}

	public function getClass(){
		return $this->ref->class;
	}

	protected function getInstance(...$args){
		$class=$this->getClass();
		return new $class(...$args);
	}

	public function getFunction(){
		return $this->ref->getName();
	}

	public function getReqParams(){
		return $this->ref->getNumberOfRequiredParameters();
	}

	public function getParams(){
		return $this->ref->getNumberOfParameters();
	}

	public function isParamsInfinite(){
		foreach($this->ref->getParameters() AS $ref_par){
			if($ref_par->isVariadic()) return true;
		}
		return false;
	}

	public function getUrlParams(){
		$url_params='';
		$req_params=$this->getReqParams();
		$i=0;
		foreach($this->ref->getParameters() AS $ref_par){
			$url_params.='/{'.$ref_par->getName().($ref_par->isVariadic()?'*':($i>=$req_params?'?':'')).'}';
			++$i;
		}
		return $url_params;
	}

	public function getMethod(){
		return $this->method;
	}

	/**
	 * @return bool
	 */
	public function isCallable(){
		return is_array($this->exec_params??null);
	}

	/**
	 * Ejecuta esta ruta
	 * @param mixed ...$args Parámetros que recibe el constructor de la clase
	 * @return false|mixed
	 * @throws RouteException
	 */
	public function call(...$args){
		if($this->isStarted()) return false;
		if(!$this->isCallable())
			throw new RouteException('The route cannot be executed', RouteException::CODE_EXECUTION);
		if($this->ref->isStatic()){
			$this->started=true;
			$res=forward_static_call_array([
				$this->getClass(),
				$this->getFunction()
			], $this->exec_params);
		}
		else{
			$this->started=true;
			$obj=$this->getInstance(...$args);
			$res=call_user_func_array([
				$obj,
				$this->getFunction()
			], $this->exec_params);
		}
		$this->started=false;
		return $res;
	}

	/**
	 * Convierte un método de una clase en una ruta. Si el método no es válido, devuelve NULL
	 * @param string $path_class Ruta inicial de la clase
	 * @param \ReflectionMethod $ref_fn
	 * @return Route|null
	 */
	public static function create($path_class, \ReflectionMethod $ref_fn){
		if($ref_fn->isPublic() && ($parts=Router::getMethodParts($ref_fn->getName()))){
			$r=new static($ref_fn);
			$r->path=$path_class.($parts['name']!==''?'/'.$parts['name']:'');
			$r->method=$parts['method'];
			return $r;
		}
		return null;
	}

}
