<?php

namespace MiniRouter;

use Exception\Execution;
use Exception\NotFound;

class RouterDump extends Router{
	/**
	 * Obtiene todas las rutas del endpoint actual<br>
	 * Antes de llamarlo se requiere {@see Router::prepareForHTTP()} o {@see Router::prepareForCLI()}
	 * @param null|string $method Filtrado por método
	 * @return Route[]
	 * @throws Exception
	 */
	public function dumpRoutes($method=null){
		if(!$this->_endpointReady)
			throw new Execution('Endpoint not ready');
		try{
			$routes=Route::getRoutes($this->main_namespace, $this->_path_class, $method);
		}catch(\ReflectionException $e){
			throw new NotFound('Class not found', 0, $e);
		}
		return $routes;
	}

	public function dumpEndpoints(){
		$this->_scanEndpoints($all);
		return $all;
	}

	private function _scanEndpoints(&$all=[], $subdir='', $level=0){
		$suffix=static::$endpoint_file_suffix;
		$suffix_len=strlen($suffix);
		$dirsrc=opendir($this->endpoint_dir.$subdir);
		if($dirsrc){
			while($file=readdir($dirsrc)){
				if($file=='.' || $file=='..') continue;
				$class=($subdir.'/').substr($file, 0, -$suffix_len);
				if($class && is_file($this->endpoint_dir.$class.$suffix) && substr($file, -$suffix_len)==$suffix){
					$all[]=$class;
				}
				elseif(is_dir($this->endpoint_dir.$subdir.'/'.$file) && $level<static::$max_subdir){
					self::_scanEndpoints($all, $subdir.'/'.$file, $level+1);
				}
			}
			closedir($dirsrc);
		}
	}

}
