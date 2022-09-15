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
function classloader(string $rootdir, string $prefix='', string $suffix='.php', string $namespace=''){
	$rootdir=realpath($rootdir);
	if($rootdir && is_dir($rootdir)){
		$preg_namespace='//';
		if(!empty($namespace)){
			$preg_namespace='/^\\\\?'.preg_quote($namespace.'\\').'.+/';
		}
		return spl_autoload_register(function($class_name) use ($rootdir, $prefix, $suffix, $preg_namespace){
			if(!preg_match($preg_namespace, $class_name)) return;
			$namespace=array_filter(explode('\\', $class_name));
			$class=array_pop($namespace);
			$paths=[];
			$paths[]=$rootdir.'/'.implode('/', $namespace).'/'.$prefix.$class.$suffix;
			$paths[]=$rootdir.'/'.implode('-', $namespace).'/'.$prefix.$class.$suffix;
			$paths=array_unique($paths, SORT_STRING);
			foreach($paths AS &$path){
				if(is_file($path)){
					include $path;
					return;
				}
			}
		}, true, !empty($namespace));
	}
	else{
		return false;
	}
}