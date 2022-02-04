<?php
namespace EndpointWeb;

use MiniRouter\Exception;
use MiniRouter\Response;

class r{
	protected $alias=[
		'a'=>'https://app.facturaprofesional.com/admin.php',
		'b'=>'https://app.facturaprofesional.com/administracion.php',
	];
	/**
	 * Path: /index
	 */
	/**
	 * @param $alias
	 * @param ...$params
	 * @throws Exception
	 */
	function GET_($alias, ...$params){
		if(!isset($this->alias[$alias])){
			throw new Exception(Exception::RESP_NOTFOUND, 'Alias: '.$alias);
		}
		$sub_uri='';
		if(count($params)){
			$sub_uri='/'.implode('/', $params);
		}
		if(strlen($_SERVER['QUERY_STRING'])){
			$sub_uri.='?'.$_SERVER['QUERY_STRING'];
		}
		Response::redirect($this->alias[$alias].$sub_uri.'#hey')->send_exit();
	}

}
