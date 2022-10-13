<?php

namespace MiniRouter;

class RouteException extends \Exception{
	const CODE_EXECUTION=500;
	const CODE_METHODNOTALLOWED=405;
	const CODE_NOTFOUND=404;
	const CODE_FORBIDDEN=403;

	public function getResponse(){
		if($this->code===self::CODE_METHODNOTALLOWED){
			return Response::r_text('Method not allowed. '.PHP_EOL.$this->getMessage())->httpCode(405);
		}
		elseif($this->code===self::CODE_NOTFOUND){
			return Response::r_text('URL not found. '.PHP_EOL.$this->getMessage())->httpCode(404);
		}
		elseif($this->code===self::CODE_FORBIDDEN){
			return Response::r_text('Access forbidden. '.PHP_EOL.$this->getMessage())->httpCode(403);
		}
		return Response::r_text('Execution error. '.PHP_EOL.$this->getMessage())->httpCode(500);
	}

	public static function simpleTrace(\Throwable $e, $lvl=0){
		$tab=str_repeat('    ', $lvl);
		$trace=$tab.get_class($e).' '.$e->getCode().'. '.$e->getMessage().PHP_EOL;
		foreach($e->getTrace() AS $i=>$l){
			$trace.=$tab.'#'.$i.' '.basename($l['file']??'?').'('.basename($l['line']??'?').'): '.($l['class']??'').($l['type']??'').($l['function']??'').'(';
			if(is_array($l['args']??null)){
				$trace.=implode(', ',array_map(function(&$d){
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