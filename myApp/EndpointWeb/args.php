<?php
namespace EndpointWeb;

use MiniRouter\Request;
use MiniRouter\Response;

class args{

	public function __construct(){
		global $endpoint;
		$endpoint=$this;
		$this->method=Request::getMethod();
		$this->path=Request::getPath();
		$this->headers=Request::getAllHeaders();
		if(count($_GET)) $this->get=$_GET;
		if(!in_array($_SERVER['REMOTE_ADDR'], ['::1'])){
//			Response::text('IP no autorizada')->http_code(504)->send_exit();
		}
	}

	function GET_(){
		print_r($this);
		return Response::text(ob_get_contents());
	}

	static function GET_globals(){
		print_r($GLOBALS);
		return Response::text(ob_get_contents());
	}
}
