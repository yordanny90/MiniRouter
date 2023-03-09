<?php

namespace Web;

class globals{
	static function GET_(){
		echo '<pre>'.print_r($GLOBALS, 1).'</pre>';
		return \AppResponse::r_html('')->includeBuffer(1);
	}

	static function GET_json(){
		$json=$GLOBALS;
		unset($json['GLOBALS']);
		return \AppResponse::r_json($json);
	}
}