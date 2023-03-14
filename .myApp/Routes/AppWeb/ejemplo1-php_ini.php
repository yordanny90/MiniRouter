<?php

namespace AppWeb\ejemplo1;

use AppWeb\ejemplo1;
use MiniRouter\Response;

class php_ini extends ejemplo1{

	static function GET_(...$_){
		echo '<pre>'.json_encode(ini_get_all(), JSON_PRETTY_PRINT).'</pre>';
		return \AppResponse::r_html('', true);
	}

	function GET_json($download=null){
		$r=Response::r_json(ini_get_all());
		if($download) $r->download('php_ini.json');
		return $r;
	}
}