<?php

namespace AppWeb;

use MiniRouter\Request;
use MiniRouter\Response;

class ejemplo1{
	static function GET_(...$_){
		echo '<h1>Esto es un ejemplo</h1>';
		?>
		<div><a href="<?=Request::getRealScript()?>/ejemplo1.info">info</a></div>
		<div><a href="<?=APP_SCRIPT?>/ejemplo1.this">this</a></div>
		<div><a href="<?=APP_SCRIPT?>/ejemplo1.php_ini">php_ini</a></div>
		<div><a href="<?=APP_SCRIPT?>/ejemplo1.php_ini.json">php_ini.json</a></div>
		<div><a href="<?=APP_SCRIPT?>/ejemplo1.php_ini.json/1">php_ini.json (download)</a></div>
		<div><a href="<?=APP_SCRIPT?>/ejemplo1.globals">globals</a></div>
		<div><a href="<?=APP_SCRIPT?>/ejemplo1.globals.json">globals.json</a></div>
		<?php
		return \AppResponse::r_html('', true);
	}

	static function GET_info(){
		phpinfo();
		return \AppResponse::r_html('', true);
	}

	function GET_this(...$_){
		$this->method=Request::getMethod();
		$this->path=Request::getPath();
		$this->headerRequest=Request::getAllHeaders();
		$this->headerResponse=Response::getHeaderList();
		if(count($_GET)) $this->get=$_GET;
		echo '<pre>'.htmlentities(print_r($this, 1)).'</pre>';
		return \AppResponse::r_html('', true);
	}

}