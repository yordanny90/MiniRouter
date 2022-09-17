<?php

namespace MiniRouter;
/**
 * Provee las funciones adicionales para la lectura de datos de la ejecución por CLI
 * Class RequestCLI
 * @package MiniRouter
 */
class RequestCLI{
	const REGEX_ARG_VAR='/^([^:=]+)[:=]((?:.|\s)*)$/';
	const REGEX_ARG_FLAG='/^\-(\w+)$/';
	const REGEX_ARG_FLAG_LONG='/^\-\-(\w+)$/';

	public static function &getArgs(){
		if(isset($_SERVER['argv'])) $args=$_SERVER['argv'];
		else $args=[];
		return $args;
	}

	/**
	 * Obtiene un argumento recibido por linea de comandos. Solo funcional si se cumple {@see Request::isCLI()}
	 * @param int $index
	 * @return string|null
	 */
	public static function getArg($index){
		$args=&static::getArgs();
		return (isset($args[$index])?$args[$index]:null);
	}

	public static function getArgVar($index){
		$args=&static::getArgsVars();
		return (isset($args[$index])?$args[$index]:null);
	}

	public static function getArgFlag($index){
		$args=&static::getArgsFlags();
		return (isset($args[$index])?$args[$index]:null);
	}

	public static function getArgText($index){
		$args=&static::getArgsText();
		return (isset($args[$index])?$args[$index]:null);
	}

	public static function &getArgsVars(){
		$list=[];
		foreach(static::getArgs() AS $i=>$arg){
			if($i>0 && preg_match(static::REGEX_ARG_VAR, $arg, $matches)){
				$list[$matches[1]]=$matches[2];
			}
		}
		return $list;
	}

	public static function &getArgsFlags(){
		$list=[];
		foreach(static::getArgs() AS $i=>$arg){
			if($i>0 && preg_match(static::REGEX_ARG_FLAG, $arg, $matches)){
				$list=array_merge($list, array_fill_keys(str_split($matches[1]),true));
			}
			elseif($i>0 && preg_match(static::REGEX_ARG_FLAG_LONG, $arg, $matches)){
				$list[$matches[1]]=true;
			}
		}
		return $list;
	}

	public static function &getArgsText(){
		$list=[];
		foreach(static::getArgs() AS $i=>$arg){
			if($i>0 && !preg_match(static::REGEX_ARG_VAR, $arg) && !preg_match(static::REGEX_ARG_FLAG, $arg) && !preg_match(static::REGEX_ARG_FLAG_LONG, $arg)){
				$list[]=$arg;
			}
		}
		return $list;
	}
}

