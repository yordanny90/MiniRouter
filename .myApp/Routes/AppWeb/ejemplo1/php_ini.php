<?php

namespace AppWeb\ejemplo1;

use MiniRouter\Response;

class php_ini{

	static function GET_(){
		echo '<pre>'.json_encode(ini_get_all(), JSON_PRETTY_PRINT).'</pre>';
		return \AppResponse::r_html('', true);
	}

	function GET_json($download=null){
		$r=Response::r_json(ini_get_all());
		if($download) $r->download('php_ini.json');
		return $r;
	}
}