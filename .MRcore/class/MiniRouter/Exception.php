<?php

namespace MiniRouter;

class Exception extends \Exception{
	const RESP_EXECUTION='MiniRouter/Responses/Execution';
	const RESP_METHODNOTALLOWED='MiniRouter/Responses/MethodNotAllowed';
	const RESP_MISSINGPARAM='MiniRouter/Responses/MissingParam';
	const RESP_NOTFOUND='MiniRouter/Responses/NotFound';

	protected $response;

	public function __construct($response, $message="", $code=0, \Throwable $previous=null){
		parent::__construct($message, $code, $previous);
		$this->response=$response;
	}

	public function getResponse(): Response{
		$res=$this->response;
		if(is_string($res)){
			$res=Dataset::get($this->response);
			if($res) $res=$res->data(['exception'=>&$this]);
		}
		if(!is_a($res, Response::class)){
			$res=Response::text($this->response.PHP_EOL.$this->getMessage())->http_code(500);
		}
		return $res;
	}
}