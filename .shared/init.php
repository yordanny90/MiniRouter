<?php
if(defined('_SHAREDDIR_')) throw new ParseError('Shared already loaded');
define('_SHAREDDIR_', __DIR__);
\MiniRouter\classloader(_SHAREDDIR_.'/class');
