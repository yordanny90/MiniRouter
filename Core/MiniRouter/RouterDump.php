<?php

namespace MiniRouter;

class RouterDump extends Router{
	/**
	 * Obtiene todas las rutas del endpoint actual<br>
	 * Antes de llamarlo se requiere {@see Router::loadEndPoint()}
	 * @param null|string $method Filtrado por método
	 * @return Route[]
	 * @throws Exception
	 */
	public function dumpRoutes($method=null){
		if(!$this->_endpoint_class)
			throw new NotFound('Class not found');
		try{
			$routes=Route::getRoutes($this->main_namespace, $this->_endpoint_class, $method);
		}catch(\ReflectionException $e){
			throw new NotFound('Class not found', 0, $e);
		}
		return $routes;
	}

	public function dumpEndpoints($endpoint_dir){
		$all=[];
		$this->_scanEndpoints($endpoint_dir.$this->main_namespace, $all);
		return $all;
	}

	private function _scanEndpoints($endpoint_dir, &$all=[], $subdir='', $level=0){
		$suffix=static::$endpoint_file_suffix;
		$suffix_len=strlen($suffix);
		$dirsrc=opendir($endpoint_dir.$subdir);
		if($dirsrc){
			while($file=readdir($dirsrc)){
				if($file=='.' || $file=='..') continue;
				$class=($subdir.'/').substr($file, 0, -$suffix_len);
				if($class && is_file($endpoint_dir.$class.$suffix) && substr($file, -$suffix_len)==$suffix){
					$all[]=$class;
				}
				elseif(is_dir($endpoint_dir.$subdir.'/'.$file) && $level<static::$max_subdir){
					self::_scanEndpoints($endpoint_dir, $all, $subdir.'/'.$file, $level+1);
				}
			}
			closedir($dirsrc);
		}
	}

}
