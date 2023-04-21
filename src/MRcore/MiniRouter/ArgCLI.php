<?php

namespace MiniRouter;
/**
 * Provee las funciones adicionales para la lectura de datos de la ejecución por CLI
 */
class ArgCLI{
	/**
	 * Expresiones regulares para validar nombres de argumentos
	 */
	const REGEX_NAME_VAR='/^\-\w+$/';
	const REGEX_NAME_FLAG='/^\-[\w\?]$/';
	const REGEX_NAME_FLAG_LONG='/^\-\-\w+$/';
	/**
	 * Expresiones regulares para analizar argumentos
	 */
	const REGEX_ARG_VAR='/^(\-\w+)[\:]/';
	const REGEX_ARG_FLAG='/^\-([\w\?]+)$/';
	const REGEX_ARG_FLAG_LONG='/^\-\-(\w+)$/';

	public static function getScript(){
		return $_SERVER['argv'][0]??null;
	}

	/**
	 * Obtiene el argumento sin analizar según su posición
	 * @param int $index
	 * @return string|null
	 */
	public static function get(int $index){
		if($index<0) return null;
		$args=$_SERVER['argv']??[];
		return $args[$index+1]??null;
	}

	/**
	 * @return array[]
	 */
	public static function &getAll(){
		$flags=[];
		$vars=[];
		$texts=[];
		$all=[
			'FLAGS'=>&$flags,
			'VARS'=>&$vars,
			'TEXTS'=>&$texts,
		];
		foreach($_SERVER['argv']??[] AS $i=>&$arg){
			if($i==0) continue;
			if(preg_match(static::REGEX_ARG_FLAG, $arg, $m)){
				$flags=array_merge($flags, array_map(function($v){ return '-'.$v; }, str_split($m[1])));
			}
			elseif(preg_match(static::REGEX_ARG_FLAG_LONG, $arg)){
				$flags[]=$arg;
			}
			elseif(preg_match(static::REGEX_ARG_VAR, $arg, $m)){
				$vars[$m[1]]=$list[$m[1]]??substr($arg, strlen($m[0]));
			}
			else{
				$texts[]=$arg;
			}
		}
		return $all;
	}

	/**
	 * @return array
	 */
	public static function &getVariables(){
		$list=[];
		foreach($_SERVER['argv']??[] AS $i=>&$arg){
			if($i==0) continue;
			if(!preg_match(static::REGEX_ARG_VAR, $arg, $m)) continue;
			$list[$m[1]]=$list[$m[1]]??substr($arg, strlen($m[0]));
		}
		return $list;
	}

	/**
	 * @param string $name
	 * @return false|string|null Devuelve NULL si el nombre no es válido, o false si no se encuentra
	 */
	public static function getVariable(string $name){
		if(!preg_match(self::REGEX_NAME_VAR, $name)) return null;
		foreach($_SERVER['argv']??[] AS $i=>&$arg){
			if($i==0) continue;
			if(!preg_match(static::REGEX_ARG_VAR, $arg, $m)) continue;
			if($name===$m[1]) return substr($arg, strlen($m[0]));
		}
		return false;
	}

	/**
	 * @return array
	 */
	public static function &getFlags(){
		$list=[];
		foreach($_SERVER['argv']??[] AS $i=>&$arg){
			if($i==0) continue;
			if(preg_match(static::REGEX_ARG_FLAG, $arg, $m)){
				$flags=array_map(function($v){ return '-'.$v; }, str_split($m[1]));
				$list=array_merge($list, $flags);
			}
			elseif(preg_match(static::REGEX_ARG_FLAG_LONG, $arg)){
				$list[]=$arg;
			}
		}
		return $list;
	}

	/**
	 * @param string $name Ejemplos: "-A" (guión y una letra) para flags cortos, "--Arg" (dos guiones y el menos una letra) para flags largos
	 * @return bool|null Devuelve NULL si el nombre no es válido
	 */
	public static function getFlag(string $name){
		$flag=false;
		if(preg_match(self::REGEX_NAME_FLAG, $name)) $short=true;
		elseif(preg_match(self::REGEX_NAME_FLAG_LONG, $name)) $short=false;
		else return null;
		foreach($_SERVER['argv']??[] AS $i=>&$arg){
			if($i==0) continue;
			if($short && preg_match(static::REGEX_ARG_FLAG, $arg, $m)){
				$flags=array_map(function($v){ return '-'.$v; }, str_split($m[1]));
				if(in_array($name, $flags, true)) return true;
			}
			elseif(!$short && $arg===$name){
				return true;
			}
		}
		return $flag;
	}

	/**
	 * @return array
	 */
	public static function &getTexts(){
		$list=[];
		foreach($_SERVER['argv']??[] AS $i=>&$arg){
			if($i==0 || preg_match(static::REGEX_ARG_VAR, $arg) || preg_match(static::REGEX_ARG_FLAG, $arg) || preg_match(static::REGEX_ARG_FLAG_LONG, $arg)) continue;
			$list[]=$arg;
		}
		return $list;
	}

	/**
	 * @param int $index
	 * @return null|string
	 */
	public static function getText(int $index){
		if($index<0) return null;
		foreach($_SERVER['argv']??[] AS $i=>&$arg){
			if($i==0 || preg_match(static::REGEX_ARG_VAR, $arg) || preg_match(static::REGEX_ARG_FLAG, $arg) || preg_match(static::REGEX_ARG_FLAG_LONG, $arg)) continue;
			if($index==0) return $arg;
			--$index;
		}
		return null;
	}
}