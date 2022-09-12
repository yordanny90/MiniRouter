<?php

namespace Web;

use MiniRouter\Response;

class index{
	public function GET_(){
		return Response::r_text('Esta es la página principal');
	}
}
