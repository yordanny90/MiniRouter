<?php

namespace MiniRouter;

class MethodNotAllowed extends Exception{
	public function getResponse(): Response{
		return Response::text('Method not allowed')->http_code(405);
	}
}