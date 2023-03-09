<?php
//readfile(__DIR__.'/phar_loader.php');
$alias='MRcore.phar';
$stub=<<<CODE
<?php
Phar::mapPhar("MRcore.phar");
require "phar://MRcore.phar/loader.php";
__HALT_COMPILER();
CODE;
$phar_file=__DIR__.'/.MRcore.phar';
$phar_file_gz=$phar_file.'.gz';
unlink($phar_file);
unlink($phar_file_gz);
$phar=new Phar($phar_file, 0, $alias);
$phar->setStub($stub);
$phar->buildFromDirectory(__DIR__.'/.MRcore');
$phar->addFile(__DIR__.'/README.md', 'README.md');
$phar->compress(Phar::GZ);
$phar=null;
$phar=new Phar($phar_file_gz, 0, $alias);
$phar->setStub($stub);
