<?php

namespace AppWeb\ejemplo1;

class globals{
	static function GET_(){
		echo '<pre>'.print_r($GLOBALS, 1).'</pre>';
		return \AppResponse::r_html('', true);
	}

	function GET_json($download=null){
		$json=$GLOBALS;
		unset($json['GLOBALS']);
		$r=\AppResponse::r_json($json);
        if($download) $r->download('globals.json');
        return $r;
	}
}