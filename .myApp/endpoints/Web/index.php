<?php

namespace Web;

use MiniRouter\Response;

class index{
	public function GET_(){
		return Response::r_text('Si ve esto, su proyecto funciona correctamente');
	}
}
