<?php

namespace MiniRouter;

class ParamMissing extends Exception{
	public function getResponse(): Response{
		return Response::text('Missing parts in URL')->http_code(400);
	}
}