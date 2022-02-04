<?php
require_once __DIR__.'/classloader_helper.php';
classloader(__DIR__.'/class', '.php', 'MiniRouter');
\MiniRouter\Dataset::register_dir(__DIR__.'/dataset', false);
