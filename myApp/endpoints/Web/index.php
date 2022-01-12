<?php
namespace Web;

class index{
	public $data=[];

	/**
	 * Peth: /index/data
	 * @param mixed ...$a
	 */
	function GET_data(...$a){
		$this->data=$a;
		print_r([__METHOD__,$this,func_get_args()]);
	}

	function GET_(){
		print_r([__METHOD__,$this,func_get_args()]);
	}

}
