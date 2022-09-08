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
			throw new \AppException(\AppException::RESP_NOTFOUND,'Class');
		try{
			$routes=Route::getRoutes($this->main_namespace, $this->_endpoint_class, $method);
		}catch(\ReflectionException $e){
			throw new \AppException(\AppException::RESP_NOTFOUND,'Class', 0, $e);
		}
		return $routes;
	}

	public function dumpClasses(){
		$all=[];
		$this->_scanEndpoints(_APPDIR_.'/'.$this->main_namespace, $all);
		return $all;
	}

	private function _scanEndpoints($endpoint_dir, &$all=[], $subdir='', $level=0){
		$prefix=static::$endpoint_file_prefix;
		$suffix=static::$endpoint_file_suffix;
		$prefix_len=strlen($prefix);
		$suffix_len=strlen($suffix);
		$dirsrc=opendir($endpoint_dir.$subdir);
		if($dirsrc){
			while($file=readdir($dirsrc)){
				if($file=='.' || $file=='..') continue;
				$class=($subdir.'/').substr($file, $prefix_len, -$suffix_len);
				if($class && is_file($endpoint_dir.$prefix.$class.$suffix) && substr($file, 0, $prefix_len)==$prefix && substr($file, -$suffix_len)==$suffix){
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
