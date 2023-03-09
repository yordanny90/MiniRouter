<?php

namespace Cron;

use MiniRouter\ArgCLI;

class index{
	static function CLI_(){
		global $ROUTE;
		echo 'Ruta actual:'.PHP_EOL.'  '.$ROUTE->getPath().PHP_EOL;
		echo 'Otras rutas:'.PHP_EOL;
		echo '  '.$ROUTE->getPathClass().'.explain'.PHP_EOL;
		echo '  '.$ROUTE->getPathClass().'.globals'.PHP_EOL;
	}

	static function CLI_explain(...$_){
		echo 'Params:',PHP_EOL;
		foreach($_ AS $v){
			echo '  '.$v.PHP_EOL;
		}
		echo 'Flags:',PHP_EOL;
		foreach(ArgCLI::getFlags() AS $v){
			echo '  '.$v.PHP_EOL;
		}
		echo 'Vars:',PHP_EOL;
		foreach(ArgCLI::getVariables() AS $k=>$v){
			echo '  '.$k.'='.var_export($v, 1).PHP_EOL;
		}
		echo 'Texts:',PHP_EOL;
		foreach(ArgCLI::getText() AS $v){
			echo '  '.var_export($v, 1).PHP_EOL;
		}
	}

	static function CLI_globals(){
		print_r($GLOBALS);
	}

}