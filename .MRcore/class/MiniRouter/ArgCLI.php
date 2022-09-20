<?php

namespace MiniRouter;
/**
 * Provee las funciones adicionales para la lectura de datos de la ejecución por CLI
 */
class ArgCLI{
	const REGEX_ARG_VAR='/^([^\=]+)\=((?:.|\s)*)$/';
	const REGEX_ARG_FLAG='/^\-(\w+)$/';
	const REGEX_ARG_FLAG_LONG='/^\-\-(\w+)$/';

	public static function getScript(){
		return $_SERVER['argv'][0]??null;
	}

	/**
	 * @param int|null $index
	 * @return array|string|null
	 */
	public static function getArgs(?int $index=null){
		$args=$_SERVER['argv']??[];
		array_shift($args);
		if(!is_null($index)) return $args[$index]??null;
		return $args;
	}

	/**
	 * @param string|null $index
	 * @return array|string|null
	 */
	public static function getVariables(?string $index=null){
		$list=is_null($index)?[]:null;
		foreach($_SERVER['argv']??[] AS $i=>&$arg){
			if($i==0 || !preg_match(static::REGEX_ARG_VAR, $arg, $matches)) continue;
			if(is_null($index)){
				$list[$matches[1]]=$list[$matches[1]]??$matches[2];
			}
			else{
				if($index===$matches[1]) return $matches[2];
			}
		}
		return $list;
	}

	/**
	 * @param string|null $index
	 * @return array|bool
	 */
	public static function getFlags(?string $index=null){
		$list=is_null($index)?[]:false;
		foreach($_SERVER['argv']??[] AS $i=>&$arg){
			if($i==0) continue;
			if(preg_match(static::REGEX_ARG_FLAG, $arg, $matches)){
				$flags=array_map(function($v){ return '-'.$v; }, str_split($matches[1]));
				if(is_null($index)){
					$list=array_merge($list, $flags);
				}
				else{
					if(in_array($index, $flags, true)) return true;
				}
			}
			elseif($i>0 && preg_match(static::REGEX_ARG_FLAG_LONG, $arg, $matches)){
				if(is_null($index)){
					$list[]=$matches[0];
				}
				else{
					if($matches[0]==$index) return true;
				}
			}
		}
		return $list;
	}

	/**
	 * @param int|null $index
	 * @return null|array|string
	 */
	public static function getText(?int $index=null){
		$list=is_null($index)?[]:null;
		foreach($_SERVER['argv']??[] AS $i=>&$arg){
			if($i==0 || preg_match(static::REGEX_ARG_VAR, $arg) || preg_match(static::REGEX_ARG_FLAG, $arg) || preg_match(static::REGEX_ARG_FLAG_LONG, $arg)) continue;
			if(is_null($index)){
				$list[]=$arg;
			}
			else{
				if($index<=0) return $arg;
				--$index;
			}
		}
		return $list;
	}
}

