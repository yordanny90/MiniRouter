<?php
/**
 * Establece una carpeta desde la que se intenta cargar un class, trait o interface que aún no existe
 * @param string $rootdir
 * @param string $suffix
 * @return bool
 */
function classloader(string $rootdir, string $suffix='.php'){
	$rootdir=realpath($rootdir);
	if($rootdir && is_dir($rootdir)){
		return spl_autoload_register(function($class_name) use ($rootdir, $suffix) {
			if(is_file($path=$rootdir.'/'.str_replace('\\', '/', $class_name).$suffix)){
				include $path;
			}
		});
	}
	else{
		return false;
	}
}