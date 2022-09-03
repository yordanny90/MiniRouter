<?php
require_once __DIR__.'/../MRcore/init.php';
require __DIR__.'/server/init.priv.php'; // Configuración propia del servidor
\MiniRouter\classloader(__DIR__.'/class');
\MiniRouter\Dataset::register_dir(__DIR__.'/dataset');
