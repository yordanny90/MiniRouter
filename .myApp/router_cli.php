<?php
require_once __DIR__.'/init.php';
\MiniRouter\Sample::router_cli();
print_r(error_get_last());
