<?php

namespace EndpointWeb;

use MiniRouter\Dataset;

class index{
	static function GET_(){
		$title=Dataset::get('main')->key('title');
		?>
		<title><?=$title?></title>
		<?php
		echo '<pre>'.print_r(Dataset::getData('example'), 1).'</pre>';
	}

	static function GET_info(){
		phpinfo();
	}

	static function GET_ini(){
		echo '<pre>';
		echo json_encode(ini_get_all(), JSON_PRETTY_PRINT);
		echo '</pre>';
	}

}
