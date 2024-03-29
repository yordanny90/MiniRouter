<?php

namespace MiniRouter;
/**
 * Provee las funciones básicas para la lectura de datos del Request
 * Class Request
 * @package MiniRouter
 */
class Request{
	const CONTENT_TYPE_NONE='';
	const CONTENT_TYPE_PLAIN='text/plain';
	const CONTENT_TYPE_HTML='text/html';
	const CONTENT_TYPE_JSON='application/json';
	const CONTENT_TYPE_JSONP='application/javascript';
	const CONTENT_TYPE_XML='application/xml';
	const CONTENT_TYPE_FORM_URLENCODED='application/x-www-form-urlencoded';
	const CONTENT_TYPE_FORM_DATA='multipart/form-data';

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
		return $_SERVER['REQUEST_SCHEME']??null;
	}

	public static function getScript($fullpath=false){
		return ($fullpath?$_SERVER['DOCUMENT_ROOT']:'').$_SERVER['SCRIPT_NAME'];
	}

	public static function getRealScript(){
		return (is_file(static::getScript(true))?static::getScript():'');
	}

	public static function getScriptFile(){
		return $_SERVER['SCRIPT_FILENAME'];
	}

	public static function getRootDir(){
		return $_SERVER['DOCUMENT_ROOT']??null;
	}

	public static function hasChanged_rootDir(){
		return getcwd()!==static::getRootDir();
	}

	public static function getPathInfo(){
		if(($_SERVER['PATH_INFO']??'')!=='' || !preg_match('/nginx/i', $_SERVER['SERVER_SOFTWARE']??'')) return $_SERVER['PATH_INFO']??null;
		# Fix para nginx
		$valid=false;
		$preg='/^'.preg_quote(static::getRealScript(), '/').'(\/[^\?]*)/';
		if(!$valid && isset($_SERVER['PHP_SELF'])) $valid=preg_match($preg, $_SERVER['PHP_SELF'], $m);
		if(!$valid && isset($_SERVER['REQUEST_URI'])) $valid=preg_match($preg, $_SERVER['REQUEST_URI'], $m);
		if(!$valid && isset($_SERVER['REDIRECT_URL'])) $valid=preg_match($preg, $_SERVER['REDIRECT_URL'], $m);
		if($valid){
			$path_info=preg_replace('/\/{2,}/','/', urldecode($m[1]));
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