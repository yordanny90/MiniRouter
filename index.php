<?php
if(!define('BASE_DIR', __DIR__)) throw new Exception('BASE already loaded', 1);
if(!define('APP_SCRIPT', basename(__FILE__))) throw new Exception('APP already loaded', 1);
include BASE_DIR.'/.myApp/web_router.php';
