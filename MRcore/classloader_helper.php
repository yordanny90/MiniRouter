<?php
/**
 * Establece una carpeta desde la que se intenta cargar un class, trait o interface que aún no existe
 * @param string $rootdir
 * @param string $suffix
 * @param string $namespace
 * @return bool
 */
function classloader(string $rootdir, string $suffix='.php', string $namespace=''){
	$rootdir=realpath($rootdir);
	if($rootdir && is_dir($rootdir)){
		$preg_namespace='//';
		if(!empty($namespace)){
			$preg_namespace='/^'.preg_quote($namespace.'\\').'/';
		}
		return spl_autoload_register(function($class_name) use ($rootdir, $suffix, $preg_namespace) {
			if(preg_match($preg_namespace, $class_name) && is_file($path=$rootdir.'/'.str_replace('\\', '/', $class_name).$suffix)){
				include $path;
			}
		});
	}
	else{
		return false;
	}
}