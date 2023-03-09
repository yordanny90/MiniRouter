<?php

namespace MiniRouter;
/**
 * Establece una carpeta desde la que se intenta cargar un class, trait o interface que aún no existe
 * @param string $rootdir
 * @param string $prefix
 * @param string $suffix
 * @param string $namespace
 * @return bool
 */
function classloader(string $rootdir, string $prefix='', string $suffix='.php', string $namespace='', bool $prepend=false){
	$rootdir=realpath($rootdir)?:$rootdir;
	if($rootdir && is_dir($rootdir)){
		$preg_namespace=null;
		if(!empty($namespace)){
			$preg_namespace='/^\\\\?'.preg_quote($namespace.'\\').'.+/';
		}
		return spl_autoload_register(function($class_name) use ($rootdir, $prefix, $suffix, $preg_namespace){
			if($preg_namespace && !preg_match($preg_namespace, $class_name)) return;
			$namespace=array_filter(explode('\\', $class_name));
			$class=array_pop($namespace);
			$paths=array_unique([
				$rootdir.'/'.implode('/', $namespace).'/'.$prefix.$class.$suffix,
				$rootdir.'/'.implode('-', $namespace).'/'.$prefix.$class.$suffix,
				$rootdir.'/'.implode('.', $namespace).'/'.$prefix.$class.$suffix,
			], SORT_STRING);
			foreach($paths AS &$path){
				if(is_file($path)){
					include $path;
					return;
				}
			}
		}, true, $prepend);
	}
	else{
		return false;
	}
}