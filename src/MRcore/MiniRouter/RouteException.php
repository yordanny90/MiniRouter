<?php

namespace MiniRouter;

class RouteException extends \Exception{
	const CODE_BADREQUEST=400;
	const CODE_UNAUTHORIZED=401;
	const CODE_FORBIDDEN=403;
	const CODE_NOTFOUND=404;
	const CODE_METHODNOTALLOWED=405;
	const CODE_EXECUTION=500;

	/**
	 * @return Response|null
	 */
	final public function getResponse(){
		$res=null;
		if($this->code===self::CODE_BADREQUEST){
			$res=$this->responseBadRequest();
		}
		elseif($this->code===self::CODE_UNAUTHORIZED){
			$res=$this->responseUnauthorized();
		}
		elseif($this->code===self::CODE_FORBIDDEN){
			$res=$this->responseForbidden();
		}
		elseif($this->code===self::CODE_NOTFOUND){
			$res=$this->responseNotFound();
		}
		elseif($this->code===self::CODE_METHODNOTALLOWED){
			$res=$this->responseMethodNotAllowed();
		}
		elseif($this->code===self::CODE_EXECUTION){
			$res=$this->responseExecution();
		}
		if(!$res) $res=$this->responseDefault();
		return $res;
	}

	/**
	 * @return Response
	 */
	protected function responseBadRequest(){
		return Response::r_text('Bad request. '.PHP_EOL.$this->getMessage())->httpCode($this->code);
	}

	/**
	 * @return Response
	 */
	protected function responseUnauthorized(){
		return Response::r_text('Unauthorized. '.PHP_EOL.$this->getMessage())->httpCode($this->code);
	}

	/**
	 * @return Response
	 */
	protected function responseForbidden(){
		return Response::r_text('Access forbidden. '.PHP_EOL.$this->getMessage())->httpCode($this->code);
	}

	/**
	 * @return Response
	 */
	protected function responseNotFound(){
		return Response::r_text('Route not found. '.PHP_EOL.$this->getMessage())->httpCode($this->code);
	}

	/**
	 * @return Response
	 */
	protected function responseMethodNotAllowed(){
		return Response::r_text('Method not allowed. '.PHP_EOL.$this->getMessage())->httpCode($this->code);
	}

	/**
	 * @return Response
	 */
	protected function responseExecution(){
		return Response::r_text('Execution error. '.PHP_EOL.$this->getMessage())->httpCode($this->code);
	}

	/**
	 * @return Response
	 */
	protected function responseDefault(){
		return Response::r_text('Error. '.PHP_EOL.$this->getMessage())->httpCode(500);
	}

	public static function simpleTrace(\Throwable $e, $lvl=0){
		$tab=str_repeat('    ', $lvl);
		$trace=$tab.get_class($e).' '.$e->getCode().'. '.$e->getMessage().PHP_EOL;
		foreach($e->getTrace() as $i=>$l){
			$trace.=$tab.'#'.$i.' '.basename($l['file'] ?? '?').'('.basename($l['line'] ?? '?').'): '.($l['class'] ?? '').($l['type'] ?? '').($l['function'] ?? '').'(';
			if(is_array($l['args'] ?? null)){
				$trace.=implode(', ', array_map(function($d){
					$type=gettype($d);
					if($type==='object') $type=get_class($d);
					return $type;
				}, $l['args']));
			}
			$trace.=')'.PHP_EOL;
		}
		if($e->getPrevious() && $lvl<10){
			$trace.=self::simpleTrace($e->getPrevious(), $lvl+1);
		}
		return $trace;
	}
}