<?php

namespace MiniRouter;

class Dataset implements \JsonSerializable{
	protected static $dir_list=[];
	protected $file;

	private function __construct(?string $file){
		$this->file=$file;
	}

	/**
	 * @param string|null $file
	 * @return Dataset|null
	 */
	public static function open(?string $file){
		if(is_file($file)){
			return new self($file);
		}
		return null;
	}

	public function getFile(){
		return $this->file;
	}

	public function data(array $params=[]){
		if(!is_string($this->file)) return null;
		extract($params);
		return (include $this->file);
	}

	public function key($key){
		$data=$this->data();
		if(is_object($data) && isset($data->$key)){
			return $data->$key;
		}
		if(is_array($data) && isset($data[$key])){
			return $data[$key];
		}
		return null;
	}

	public function jsonSerialize(){
		return $this->data();
	}

	static function is_registered_dir($dirname){
		$dir=realpath($dirname);
		return $dir && in_array($dir, self::$dir_list);
	}

	static function register_dir($dirname, $prepend=true){
		$dir=realpath($dirname);
		if($dir && is_dir($dir)){
			if(!in_array($dir, self::$dir_list)){
				if($prepend) array_unshift(self::$dir_list, $dir);
				else self::$dir_list[]=$dir;
				return true;
			}
		}
		return false;
	}

	static function unregister_dir($dirname){
		$dir=realpath($dirname);
		if($dir){
			$count=count(self::$dir_list);
			self::$dir_list=array_values(array_diff(self::$dir_list, [$dir]));
			if($count!=count(self::$dir_list)){
				return true;
			}
		}
		return false;
	}

	static function get($nombre): ?self{
		foreach(self::$dir_list as &$dir){
			$file=$dir.'/'.$nombre.'.php';
			if($ds=self::open($file)){
				return $ds;
			}
		}
		return null;
	}

	static function getData($nombre, array $params = []){
		$set=self::get($nombre);
		if($set) return $set->data($params);
		return null;
	}

	static function getAll($nombre, $file_as_key=false): array{
		$list=[];
		foreach(self::$dir_list as &$dir){
			$file=$dir.'/'.$nombre.'.php';
			if($ds=self::open($file)){
				if($file_as_key) $list[$file]=$ds;
				else $list[]=$ds;
			}
		}
		return $list;
	}

	static function getAllDataMerged($nombre, array $params = []): array{
		$data=[];
		foreach(self::$dir_list as &$dir){
			$file=$dir.'/'.$nombre.'.php';
			if($ds=self::open($file)){
				$data=array_merge($data, $ds->data($params));
			}
		}
		return $data;
	}

}