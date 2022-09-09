<?php

namespace MiniRouter;

abstract class RouteException extends \Exception{
	const RESP_EXECUTION='Execution';
	const RESP_METHODNOTALLOWED='MethodNotAllowed';
	const RESP_NOTFOUND='NotFound';

	protected $type_resp;

	/**
	 * @param mixed $type_resp Tipo de mensaje de respuesta {@see RouteException::getTypeResp()}
	 * @param $message
	 * @param $code
	 * @param \Throwable|null $previous
	 */
	public function __construct($type_resp, $message="", $code=0, \Throwable $previous=null){
		parent::__construct($message, $code, $previous);
		$this->type_resp=$type_resp;
	}

	/**
	 * @return mixed Tipo de mensaje de respuesta
	 * @see RouteException::RESP_EXECUTION
	 * @see RouteException::RESP_METHODNOTALLOWED
	 * @see RouteException::RESP_NOTFOUND
	 */
	public function getTypeResp(){
		return $this->type_resp;
	}

	final public function getResponse(){
		if($this->type_resp===static::RESP_EXECUTION){
			return $this->respExecution();
		}
		elseif($this->type_resp===static::RESP_METHODNOTALLOWED){
			return $this->respMethodNotAllowed();
		}
		elseif($this->type_resp===static::RESP_NOTFOUND){
			return $this->respNotFound();
		}
		return $this->respGeneric();
	}

	/**
	 * @return Response
	 */
	protected function respNotFound(){
		return Response::r_text('URL not found. '.PHP_EOL.$this->getMessage())->httpCode(404);
	}

	/**
	 * @return Response
	 */
	protected function respMethodNotAllowed(){
		return Response::r_text('Method not allowed. '.PHP_EOL.$this->getMessage())->httpCode(405);
	}

	/**
	 * @return Response
	 */
	protected function respExecution(){
		return Response::r_text('Execution error. '.PHP_EOL.$this->getMessage())->httpCode(500);
	}

	/**
	 * @return Response
	 */
	protected function respGeneric(){
		return Response::r_text('Error ('.$this->getTypeResp().'). '.$this->getMessage())->httpCode(500);
	}
}