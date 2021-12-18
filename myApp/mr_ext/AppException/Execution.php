<?php

namespace AppException;

class Execution extends \MiniRouter\Exception{
	public function getResponse(): \MiniRouter\Response{
		return \MiniRouter\Response::text('Internal error')->http_code(500);
	}
}