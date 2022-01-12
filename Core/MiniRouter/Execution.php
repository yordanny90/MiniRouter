<?php

namespace MiniRouter;

class Execution extends Exception{
	public function getResponse(): Response{
		return Response::text('Execution error')->http_code(500);
	}
}