<?php

namespace EndpointWeb;

use MiniRouter\Dataset;

class index{
	public $data=[];

	function GET_(){
		$title=Dataset::get('main')->key('title');
		?>
		<title><?=$title?></title>
		<?php
		echo '<pre>'.print_r(Dataset::getData('example'), 1).'</pre>';
	}

}
