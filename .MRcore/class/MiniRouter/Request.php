<?php

namespace MiniRouter;
/**
 * Provee las funciones básicas para la lectura de datos del Request
 * Class Request
 * @package MiniRouter
 */
class Request{
	const CONTENTYPE_NONE='';
	const CONTENTYPE_PLAIN='text/plain';
	const CONTENTYPE_HTML='text/html';
	const CONTENTYPE_JSON='application/json';
	const CONTENTYPE_JSONP='application/javascript';
	const CONTENTYPE_XML='application/xml';
	const CONTENTYPE_FORM_URLENCODED='application/x-www-form-urlencoded';
	const CONTENTYPE_FORM_DATA='multipart/form-data';

	private function __construct(){ }

	/**
	 * Determina si la ejecución actual es una ejecución por linea de comandos (CLI)
	 * @return bool
	 */
	public static function isCLI(){
		return (php_sapi_name()=='cli' && isset($_SERVER['argc']) && isset($_SERVER['argv']) && is_array($_SERVER['argv']));
	}

	/**
	 * Determina si el request fué realizado por Ajax (XMLHttpRequest)
	 * @return bool
	 */
	public static function isAjax(){
		return (self::getHeader('X-Requested-With')=='XMLHttpRequest');
	}

	public static function &getAcceptList(){
		$accepts=[];
		foreach(explode(',', self::getHeader('Accept')) AS $v){
			$v=explode(';', $v, 2);
			if(!isset($v[1])){
				$v[1]='';
			}
			else{
				$v[1]=str_replace(';', ";\n", $v[1]);
			}
			$accepts[$v[0]]=parse_ini_string($v[1]);
		}
		return $accepts;
	}

	public static function getMethod(){
		return (isset($_SERVER['REQUEST_METHOD'])?$_SERVER['REQUEST_METHOD']:'');
	}

	public static function getScheme(){
		return (isset($_SERVER['REQUEST_SCHEME'])?$_SERVER['REQUEST_SCHEME']:'');
	}

	/**
	 * Devuelve la url raiz del endpont actual
	 * @param bool $withHost Incluye el protocolo y el hosname
	 * @param bool $withScript Incluye el nombre del script
	 * @return string
	 */
	public static function getBaseURI($withHost=false, $withScript=false){
		return ($withHost?self::getScheme().'://'.self::getHeader('host'):'').($withScript?$_SERVER['SCRIPT_NAME']:preg_replace('/[^\/]*$/','',$_SERVER['SCRIPT_NAME']));
	}

	public static function getPath(){
		return isset($_SERVER['PATH_INFO'])?$_SERVER['PATH_INFO']:'';
	}

	static function getContentType(){
		$ct=(isset($_SERVER['CONTENT_TYPE'])?$_SERVER['CONTENT_TYPE']:'');
		return trim(explode(';', $ct, 2)[0]);
	}

	/**
	 * @return array
	 */
	static function getAllHeaders(){
		$headers=[];
		foreach($_SERVER AS $k=>$v){
			if(substr($k, 0, 5)=='HTTP_'){
				$k=mb_convert_case(str_replace('_', '-', substr($k, 5)), MB_CASE_TITLE);
				$headers[$k]=$v;
			}
		}
		return $headers;
	}

	/**
	 * @param string $name
	 * @return mixed|null
	 */
	static function getHeader($name){
		$index='HTTP_'.strtoupper(str_replace('-', '_', $name));
		if(isset($_SERVER[$index])){
			return $_SERVER[$index];
		}
		return null;
	}

}