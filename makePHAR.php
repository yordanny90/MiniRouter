<?php
$phar_file=__DIR__.'/.MRcore.phar';
//readfile(__DIR__.'/phar_loader.php');
$phar=new Phar($phar_file, 0, 'MRcore.phar');
$phar->buildFromDirectory(__DIR__.'/.MRcore');
unlink($phar_file.'.gz');
$phar->compress(Phar::GZ);
$phar=null;
unlink($phar_file);
$phar=new Phar(__DIR__.'/.MRcore.phar.gz', 0, 'MRcore.phar');
$phar->setStub(<<<PHP
<?php
Phar::mapPhar("MRcore.phar");
require "phar://MRcore.phar/loader.php";
__HALT_COMPILER();
PHP
);
