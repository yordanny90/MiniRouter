<?php
if(!define('SHARED_DIR', __DIR__)) throw new Exception('Shared already loaded', 1);
\MiniRouter\classloader(SHARED_DIR.'/class');
