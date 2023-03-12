<?php

namespace AppWeb\ejemplo1;

class globals{
	static function GET_(){
		echo '<pre>'.print_r($GLOBALS, 1).'</pre>';
		return \AppResponse::r_html('')->includeBuffer(1);
	}

	function GET_json(){
		$json=$GLOBALS;
		unset($json['GLOBALS']);
		return \AppResponse::r_json($json);
	}
}