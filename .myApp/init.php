<?php
if(!define('APP_DIR', __DIR__)) throw new Exception('App already loaded', 1);
if(!define('BASE_DIR', realpath(__DIR__.'/..'))) throw new Exception('Base already loaded', 1);
if(!chdir(BASE_DIR)) throw new Exception('Current dir not changed', 1);

// Puede usar la version comprimida para ahorrar espacio:
//require BASE_DIR.'/build/MRcore.phar.gz';
require BASE_DIR.'/build/MRcore.phar';
//require BASE_DIR.'/src/loader.php';

\MiniRouter\classloader(APP_DIR.'/class');
//\MiniRouter\classloader(BASE_DIR.'/.lib_class');
