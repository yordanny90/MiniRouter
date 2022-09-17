<?php

namespace Web;

class globals{
	static function GET_(){
		echo '<pre>'.print_r($GLOBALS, 1).'</pre>';
		return \AppResponse::r_html('')->includeBuffer(1);
	}
}