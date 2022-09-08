<?php

namespace MiniRouter;

class Dataset implements \JsonSerializable{
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

	public function get($key){
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

}