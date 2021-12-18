<?php

namespace AppException;

class ParamMissing extends \MiniRouter\Exception{
	public function getResponse(): \MiniRouter\Response{
		return \MiniRouter\Response::text('Param url missing. '.$this->getMessage())->http_code(400);
	}
}