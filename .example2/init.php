<?php
if(!defined('BASE_DIR')) throw new Exception('Base not loaded', 1);
if(!define('APP_DIR', __DIR__)) throw new Exception('App already loaded', 1);
require APP_DIR.'/server/init.priv.php'; // Configuración propia del servidor
require_once APP_DIR.'/../.MRcore/init.php';
\MiniRouter\classloader(APP_DIR.'/class');
require_once APP_DIR.'/../.shared/init.php';
