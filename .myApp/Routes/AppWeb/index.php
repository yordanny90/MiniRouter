<?php

namespace AppWeb;

use MiniRouter\Response;

class index{
	public function GET_(){
		ob_start();
		?>
		Si ve esto, su proyecto funciona correctamente
		<br>
		<div><a href="<?=APP_SCRIPT?>/ejemplo1">Ejemplo 1</a></div>
		<?php
		return Response::r_html(ob_get_clean());
	}
}
