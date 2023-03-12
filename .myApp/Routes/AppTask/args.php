<?php

namespace AppTask;

use MiniRouter\ArgCLI;

class args{
	static function CLI_(){
		global $ROUTE;
		echo 'Rutas:'.PHP_EOL;
		echo '  '.$ROUTE->getPathClass().'.show'.PHP_EOL;
	}

	static function CLI_show(...$_){
		echo 'Params: ';
		var_export($_);
		echo PHP_EOL.PHP_EOL;
		echo '### Flags: ';
		var_export(ArgCLI::getFlags());
		echo PHP_EOL.PHP_EOL;
		echo '### Vars: ';
		var_export(ArgCLI::getVariables());
		echo PHP_EOL.PHP_EOL;
		echo '### Texts: ';
		var_export(ArgCLI::getTexts());
	}

}