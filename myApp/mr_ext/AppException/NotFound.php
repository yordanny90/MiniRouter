<?php

namespace AppException;

class NotFound extends \MiniRouter\Exception{
	public function getResponse(): \MiniRouter\Response{
		return \MiniRouter\Response::text('Endpoint not found. '.$this->getMessage())->http_code(404);
	}
}