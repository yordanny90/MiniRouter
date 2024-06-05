<?php
namespace MiniRouter;

/**
 * Class Route
 * @package MiniRouter
 */
class Route{
    /**
     * @var array|null
     */
    protected $exec_params;
	/**
	 * @var string Ruta válida de la clase del Endpoint
	 */
	protected $path_class;
	/**
	 * @var string Nombre válido de la función del Endpoint
	 */
	protected $name;
	/**
	 * @var string Ruta válida del Endpoint
	 */
	protected $path;
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
     * @param array $exec_params
     */
    public function setExecParams(array $exec_params): void{
        $this->exec_params=$exec_params;
    }

	/**
	 * @return string
	 */
	public function getPath(): string{
		return $this->path;
	}

	/**
	 * @return string
	 */
	public function getPathClass(): string{
		return $this->path_class;
	}

	/**
	 * @return string
	 */
	public function getName(): string{
		return $this->name;
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
		return is_array($this->exec_params);
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
        $this->started=true;
        if($this->ref->isStatic()){
			$res=forward_static_call_array([
				$this->getClass(),
				$this->getFunction()
			], $this->exec_params);
		}
		else{
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
	 * @param $path_splitter
	 * @return static|null
	 */
	public static function create($path_class, \ReflectionMethod $ref_fn, $path_splitter='/'){
		if($ref_fn->isPublic() && ($parts=static::getMethodParts($ref_fn->getName()))){
			$r=new static($ref_fn);
			$r->path=$path_class.($parts['name']!==''?$path_splitter.$parts['name']:'');
			$r->method=$parts['method'];
			$r->path_class=$path_class;
			$r->name=$parts['name'];
			return $r;
		}
		return null;
	}

	/**
	 * @param string $fnName
	 * @return array|null Si el nombre es válido devuelve un array con dos llaves: 'method' y 'name'
	 */
	public static function getMethodParts(string $fnName){
		if(preg_match('/^([A-Z]+)_(.*)$/', $fnName, $matches)){
			return [
				'method'=>$matches[1],
				'name'=>$matches[2]
			];
		}
		return null;
	}

}
