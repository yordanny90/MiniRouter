<?php

namespace EndpointWeb;

use MiniRouter\Dataset;
use MiniRouter\Request;
use MiniRouter\Response;

class index{
	static function GET_(){
		$example=include _APPDIR_.'/dataset/example.php';
		?>
		<title><?=TITLE?></title>
		<base href="<?=BASE_URL?>">
		<?php
		echo '<pre>'.print_r($example, 1).'</pre>';
		?>
		<div><a href="index/info">info</a></div>
		<div><a href="index/ini">ini</a></div>
		<div><a href="index/this">this</a></div>
		<div><a href="index/globals">globals</a></div>
		<?php
	}

	static function GET_info(){
		echo '<div><a href="index">Atrás</a></div>';
		phpinfo();
	}

	static function GET_ini(){
		echo '<div><a href="index">Atrás</a></div>';
		echo '<pre>';
		echo json_encode(ini_get_all(), JSON_PRETTY_PRINT);
		echo '</pre>';
	}

	function GET_this(){
		$this->method=Request::getMethod();
		$this->path=Request::getPath();
		$this->headers=Request::getAllHeaders();
		if(count($_GET)) $this->get=$_GET;
		print_r($this);
		return Response::r_text('')->includeBuffer(true);
	}

	static function GET_globals(){
		print_r($GLOBALS);
		return Response::r_text('')->includeBuffer(true);
	}
}
