<?php
if(!defined('BASE_DIR')) throw new Exception('Base not loaded', 1);
if(!chdir(BASE_DIR)) throw new Exception('Current dir not changed', 1);
if(!define('APP_DIR', __DIR__)) throw new Exception('App already loaded', 1);
require APP_DIR.'/server/init.priv.php'; // Configuración propia del servidor

// Puede usar la version comprimida para ahorrar espacio:
//require_once BASE_DIR.'/.MRcore.phar.gz';
require_once BASE_DIR.'/.MRcore.phar';

\MiniRouter\classloader(APP_DIR.'/class');
//\MiniRouter\classloader(BASE_DIR.'/.lib_class');
