<?php

namespace MiniRouter;

/**
 * Class DatasetExport
 * Clase para la creación de archivos PHP que retorna un dato/valor
 */
class DatasetExport{
	private function __construct(){ }

	public static function &asString($value, $comments=null){
		$str=var_export($value, true);
		if(!is_string($str)){
			return $str;
		}
		if(!is_null($comments)) $comments="//\t".preg_replace('/(\r\n|\n|\r)/', "$1//\t", $comments);
		$str="<?php ".PHP_EOL.$comments.PHP_EOL."return ".$str.";".PHP_EOL;
		return $str;
	}

	public static function saveTo($filename, $value, $comments=null){
		$str=static::asString($value, $comments);
		if(!is_string($str)){
			return false;
		}
		return file_put_contents($filename, $str);
	}
}