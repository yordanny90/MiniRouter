<?php

use MiniRouter\Response;

// Se carga la librería del MiniRouter
require_once __DIR__.'/init.php';
Response::flushBuffer();
include 'phar://.MRcore.phar/simpleP/router_cli.php';
print_r(error_get_last());