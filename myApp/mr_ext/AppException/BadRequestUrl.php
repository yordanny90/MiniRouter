<?php

namespace AppException;

class BadRequestUrl extends \MiniRouter\Exception{
	public function getResponse(): \MiniRouter\Response{
		return \MiniRouter\Response::text('Bad request url. '.$this->getMessage())->http_code(400);
	}
}