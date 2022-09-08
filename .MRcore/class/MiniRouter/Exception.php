<?php

namespace MiniRouter;

abstract class Exception extends \Exception{
	const RESP_EXECUTION='Execution';
	const RESP_METHODNOTALLOWED='MethodNotAllowed';
	const RESP_MISSINGPARAM='MissingParam';
	const RESP_NOTFOUND='NotFound';

	protected $type_resp;

	/**
	 * @param mixed $type_resp Tipo de mensaje de respuesta {@see Exception::getTypeResp()}
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
	 * @see Exception::RESP_EXECUTION
	 * @see Exception::RESP_METHODNOTALLOWED
	 * @see Exception::RESP_MISSINGPARAM
	 * @see Exception::RESP_NOTFOUND
	 */
	public function getTypeResp(){
		return $this->type_resp;
	}

	final public function getResponse(){
		if($this->type_resp===static::RESP_EXECUTION){
			return $this->resp_execution();
		}
		elseif($this->type_resp===static::RESP_METHODNOTALLOWED){
			return $this->resp_methodnotallowed();
		}
		elseif($this->type_resp===static::RESP_MISSINGPARAM){
			return $this->resp_missingparam();
		}
		elseif($this->type_resp===static::RESP_NOTFOUND){
			return $this->resp_notfound();
		}
		return $this->resp_generic();
	}

	/**
	 * @return Response
	 */
	protected function resp_execution(){
		return Response::r_text('Execution error. '.$this->getMessage())->http_code(500);
	}

	/**
	 * @return Response
	 */
	protected function resp_methodnotallowed(){
		return Response::r_text('Method not allowed. '.$this->getMessage())->http_code(405);
	}

	/**
	 * @return Response
	 */
	protected function resp_missingparam(){
		return Response::r_text('Missing parts in URL. '.$this->getMessage())->http_code(404);
	}

	/**
	 * @return Response
	 */
	protected function resp_notfound(){
		return Response::r_text('Endpoint not found. '.$this->getMessage())->http_code(404);
	}

	/**
	 * @return Response
	 */
	protected function resp_generic(){
		return Response::r_text('Error ('.$this->getTypeResp().'). '.$this->getMessage())->http_code(500);
	}
}