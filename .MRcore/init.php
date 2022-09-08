<?php
if(defined('_MRDIR_')) throw new ParseError('MRcore already loaded');
define('_MRDIR_', __DIR__);
require_once _MRDIR_.'/helper/classloader_helper.php';
\MiniRouter\classloader(_MRDIR_.'/class', '', '.php', 'MiniRouter');
