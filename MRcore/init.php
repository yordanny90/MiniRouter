<?php
require_once __DIR__.'/helper/classloader_helper.php';
\MiniRouter\classloader(__DIR__.'/class', '', '.php', 'MiniRouter');
\MiniRouter\Dataset::register_dir(__DIR__.'/dataset', false);
