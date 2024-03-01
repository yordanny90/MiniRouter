<?php

namespace MiniRouter;

class Sample{

	private function __construct(){ }

	public static function router_cli(){
		return require DIR.'/sample/router_cli.php';
	}

	public static function router_http(){
		return require DIR.'/sample/router_http.php';
	}

}