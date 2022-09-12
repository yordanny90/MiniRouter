<?php

namespace MiniRouter;

class RouteException extends \Exception{
	const CODE_EXECUTION=500;
	const CODE_METHODNOTALLOWED=405;
	const CODE_NOTFOUND=404;
	const CODE_FORBIDDEN=403;

	public function getResponse(): Response{
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

}