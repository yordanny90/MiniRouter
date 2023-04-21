<?php
namespace MiniRouter;
if(defined('\MiniRouter\DIR')) throw new \Exception('MiniRouter already loaded', 1);
const DIR=__DIR__;
require_once DIR.'/helper/classloader_helper.php';
classloader(DIR, '', '.php', 'MiniRouter', true);
