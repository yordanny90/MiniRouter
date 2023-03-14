<?php

use MiniRouter\Response;
// Se carga la librería del MiniRouter
require_once __DIR__.'/init.php';
Response::flatBuffer();
require 'phar://.MRcore.phar/sample/router_cli.php';
print_r(error_get_last());
