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
		return (php_sapi_name()=='cli' && isset($_SERVER['argc']) && is_array($_SERVER['argv']??null));
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
		return $_SERVER['REQUEST_METHOD']??'';
	}

	public static function getScheme(){
		return $_SERVER['REQUEST_SCHEME']??'';
	}

	public static function getPath(){
		if(($_SERVER['PATH_INFO']??'')!==''){
			return $_SERVER['PATH_INFO'];
		}
		# Fix para nginx
		$path_info=$_SERVER['REQUEST_URI']??'';
		if(preg_match('/^'.preg_quote($_SERVER['SCRIPT_NAME']??'', '/').'(\/.*)$/', $path_info, $m)){
			$path_info=$m[1];
			if(preg_match('/^(\/.*)\?'.preg_quote(($_SERVER['QUERY_STRING']??''), '/').'$/', $path_info, $m)){
				$path_info=$m[1];
			}
			$path_info=preg_replace('/\/{2,}/','/', urldecode($path_info));
			return $path_info;
		}
		return null;
	}

	public static function getQuery(){
		return $_SERVER['QUERY_STRING']??'';
	}

	static function getContentType(){
		$ct=$_SERVER['CONTENT_TYPE']??'';
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
		return $_SERVER[$index]??null;
	}

}