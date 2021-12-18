<?php

namespace AppException;

class MethodNotAllowed extends \MiniRouter\Exception{
	public function getResponse(): \MiniRouter\Response{
		return \MiniRouter\Response::text('Method not allowed.')->http_code(405);
	}
}