<?php

namespace MiniRouter;

class NotFound extends Exception{
	public function getResponse(): Response{
		return Response::text('Endpoint not found')->http_code(404);
	}
}