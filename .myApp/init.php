<?php
if(!define('APP_SCRIPT', basename($_SERVER['SCRIPT_NAME']))) throw new Exception('APP_SCRIPT already loaded', 1);
if(!define('BASE_DIR', realpath(__DIR__.'/..'))) throw new Exception('Base already loaded', 1);
if(!chdir(BASE_DIR)) throw new Exception('Current dir not changed', 1);
if(!define('APP_DIR', __DIR__)) throw new Exception('App already loaded', 1);

// Puede usar la version comprimida para ahorrar espacio:
require_once BASE_DIR.'/.MRcore.phar.gz';
//require_once BASE_DIR.'/.MRcore.phar';

\MiniRouter\classloader(APP_DIR.'/class');
//\MiniRouter\classloader(BASE_DIR.'/.lib_class');
