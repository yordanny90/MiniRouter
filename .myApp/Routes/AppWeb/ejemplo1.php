<?php

namespace AppWeb;

use MiniRouter\Request;
use MiniRouter\Response;

class ejemplo1{
    protected $args;
    protected $method;
    protected $path;
    protected $headerRequest;
    protected $headerResponse;
	static function GET_(){
		echo '<h1>Esto es un ejemplo</h1>';
		?>
		<div><a href="<?=HREF?>ejemplo1.info">info</a></div>
		<div><a href="<?=HREF?>ejemplo1.this">this</a></div>
        <div><a href="<?=HREF?>ejemplo1.this/arg1/arg2">this/arg1/arg2</a></div>
		<div><a href="<?=HREF?>ejemplo1.php_ini">php_ini</a></div>
		<div><a href="<?=HREF?>ejemplo1.php_ini.json">php_ini.json</a></div>
		<div><a href="<?=HREF?>ejemplo1.php_ini.json/1">php_ini.json (download)</a></div>
		<div><a href="<?=HREF?>ejemplo1.globals">globals</a></div>
		<div><a href="<?=HREF?>ejemplo1.globals.json">globals.json</a></div>
		<?php
		return \AppResponse::r_html('', true);
	}

	static function GET_info(){
		phpinfo();
		return \AppResponse::r_html('', true);
	}

	function GET_this(...$_){
        $this->args=$_;
        $this->method=Request::getMethod();
        $this->path=Request::getPathInfo();
        $this->headerRequest=Request::getAllHeaders();
        $this->headerResponse=Response::getHeaderList();
		if(count($_GET)) $this->get=$_GET;
		echo '<pre>'.htmlentities(print_r($this, 1)).'</pre>';
		return \AppResponse::r_html('', true);
	}

}