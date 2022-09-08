<?php
if(defined('_APPDIR_')) throw new ParseError('App already loaded');
define('_APPDIR_', __DIR__);
require _APPDIR_.'/server/init.priv.php'; // Configuración propia del servidor
require_once _APPDIR_.'/../.MRcore/init.php';
require_once _APPDIR_.'/../.shared/init.php';
\MiniRouter\classloader(_APPDIR_.'/class');
