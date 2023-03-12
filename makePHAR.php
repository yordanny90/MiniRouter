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
echo "Eliminando archivos antiguos...".PHP_EOL;
unlink($phar_file);
unlink($phar_file_gz);

$t=time();
echo "Creando nuevo archivo PHAR...".PHP_EOL;
$phar=new Phar($phar_file, 0, $alias);
$phar->buildFromDirectory(__DIR__.'/.MRcore');
$phar->addFile(__DIR__.'/README.md', 'README.md');
$phar->setStub($stub);

echo "Comprimiendo archivo PHAR...".PHP_EOL;
$pharGZ=$phar->compress(Phar::GZ);
$pharGZ->setStub($stub);

echo "Completado! ".(time()-$_SERVER['REQUEST_TIME']).'s';
