<?php
namespace endpoint;

use MiniRouter\Response;
use MiniRouter\Route;

/**
 * @package endpoint
 */
class index{
	public $data=[];

	/**
	 * Path: /index
	 */
	function _GET_(){
		print_r([__METHOD__,$this,func_get_args()]);
	}

	/**
	 * Peth: /index/data
	 * @param mixed ...$a
	 */
	function GET_data(...$a){
		print_r([__METHOD__,$this,func_get_args()]);
	}

	public function __call($name, $arguments){
		print_r(Route::this());
		echo __FILE__.PHP_EOL.'Function missing:'.PHP_EOL.PHP_EOL;
		?>
	public function <?=$name?>(){
		echo 'Esta es la verdadera función';
	}
		<?php
	}

	public function __destruct(){
		Response::text('')->send_exit(1);
	}
}
