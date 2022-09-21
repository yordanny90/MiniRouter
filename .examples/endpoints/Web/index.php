<?php

namespace Web;

use MiniRouter\Request;
use MiniRouter\Response;

/**
 *
 */
class index{
	static function GET_(...$_){
		echo '<h1>Esto es un ejemplo</h1>';
		echo '<pre>'.htmlentities(print_r(include APP_DIR.'/dataset/example.php', 1)).'</pre>';
		?>
		<div><a href="<?=APP_SCRIPT?>/index/info">info</a></div>
		<div><a href="<?=APP_SCRIPT?>/index/ini">php.ini</a></div>
		<div><a href="<?=APP_SCRIPT?>/index/ini/json">php.ini (JSON)</a></div>
		<div><a href="<?=APP_SCRIPT?>/index/ini/json/1">php.ini (download JSON)</a></div>
		<div><a href="<?=APP_SCRIPT?>/index/this">this</a></div>
		<div><a href="<?=APP_SCRIPT?>/globals">globals</a></div>
		<?php
		return \AppResponse::r_html('')->includeBuffer(1)->gz(1);
	}

	static function GET_info(){
		phpinfo();
		return \AppResponse::r_html('')->includeBuffer(1);
	}

	static function GET_ini($format=null, $download=null){
		if($format=='json'){
			$r=Response::r_json(ini_get_all());
			if($download) $r->download('php.ini.json');
			return $r;
		}
		echo '<pre>'.json_encode(ini_get_all(), JSON_PRETTY_PRINT).'</pre>';
		return \AppResponse::r_html('')->includeBuffer(1);
	}

	function GET_this(...$_){
		$this->method=Request::getMethod();
		$this->path=Request::getPath();
		$this->headerRequest=Request::getAllHeaders();
		$this->headerResponse=Response::getHeaderList();
		if(count($_GET)) $this->get=$_GET;
		echo '<pre>'.htmlentities(print_r($this, 1)).'</pre>';
		return \AppResponse::r_html('')->includeBuffer(true)->gz(1);
	}

}
