<?php

namespace AppTask;

class globals{
	static function CLI_(){
		global $ROUTE;
		echo 'Rutas:'.PHP_EOL;
		echo '  '.$ROUTE->getPathClass().'.ver'.PHP_EOL;
		echo '  '.$ROUTE->getPathClass().'.ini'.PHP_EOL;
		echo '  '.$ROUTE->getPathClass().'.info'.PHP_EOL;
		echo '  '.$ROUTE->getPathClass().'.classes'.PHP_EOL;
		echo '  '.$ROUTE->getPathClass().'.functions'.PHP_EOL;
		echo '  '.$ROUTE->getPathClass().'.const'.PHP_EOL;
	}

	function CLI_ver(){
		echo phpversion();
	}

	function CLI_ini($ext=null){
		var_export(ini_get_all($ext));
	}

	function CLI_info(){
		phpinfo();
	}

	function CLI_classes(){
		var_export( get_declared_classes());
	}

	function CLI_functions(){
		var_export(get_defined_functions());
	}

	function CLI_const($cat=0){
		var_export(get_defined_constants($cat));
	}
}