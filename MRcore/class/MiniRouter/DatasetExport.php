<?php

namespace MiniRouter;

/**
 * Class DatasetExport
 * Clase para la creación de archivos PHP que retorna un dato/valor
 */
class DatasetExport{
	private function __construct(){ }

	public static function &asString($value, $comments=null){
		if(!is_null($comments)) $comments="//\t".preg_replace('/(\r\n|\n|\r)/', "$1//\t", $comments);
		$str="<?php ".PHP_EOL.$comments.PHP_EOL."return ".var_export($value, true).";".PHP_EOL;
		return $str;
	}

	public static function saveTo($filename, $value, $comments=null){
		return file_put_contents($filename, static::asString($value, $comments));
	}
}