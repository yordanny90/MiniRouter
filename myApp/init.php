<?php
require_once __DIR__.'/../Core/init.php';
classloader(__DIR__.'/mr_ext');
\MiniRouter\Router::$app_dir=__DIR__;
