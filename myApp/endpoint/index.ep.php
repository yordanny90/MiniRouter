<?php
namespace endpoint;
use MiniRouter\Response;
use MiniRouter\Request;

class index{
	public $data=[];

	public function __construct(){
		$this->input=Request::getInput();
		$this->headers=Request::getAllHeaders();
		$this->accept=Request::getAcceptList();
	}

	function GET_($id=0, $a='', $b=null){
		$this->data['argumants']=func_get_args();
		print_r($this);
	}

	function POST_(...$a){
		return Response::json($this->data);
	}

}
