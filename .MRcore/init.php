<?php
if(!define('MRCORE_DIR', __DIR__)) throw new Exception('MRcore already loaded', 1);
require_once MRCORE_DIR.'/helper/classloader_helper.php';
\MiniRouter\classloader(MRCORE_DIR.'/class', '', '.php', 'MiniRouter');
