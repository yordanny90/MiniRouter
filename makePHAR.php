<?php
ini_set('default_charset', 'UTF-8');
date_default_timezone_set('america/costa_rica');
//readfile(__DIR__.'/phar_loader.php');
$phar_file=__DIR__.'/.build/MRcore.phar';
if(ini_get('phar.readonly')){
    throw new Exception("Se requiere phar.readonly=Off en php.ini para continuar.\n".php_ini_loaded_file());
}
echo "Eliminando archivos antiguos...".PHP_EOL;
if(file_exists($phar_file)) unlink($phar_file);
if(file_exists($phar_file.'.gz')) unlink($phar_file.'.gz');

echo "Creando nuevo archivo PHAR...".PHP_EOL;
$stub='<?php require "phar://".__FILE__."/loader.php"; __HALT_COMPILER();';
$metadata=[
	'author'=>'Yordanny Mejías',
	'email'=>'yordanny90@gmail.com',
	'description'=>'MiniRouter Core',
	'version'=>'0.2',
	'update'=>date(DATE_W3C),
	'repo'=>'https://github.com/yordanny90/MiniRouter',
	'default_charset'=>ini_get('default_charset'),
];
$phar=new Phar($phar_file);
$phar->setStub($stub);
$phar->setMetadata($metadata);
$phar->buildFromDirectory(__DIR__.'/src');
//$phar->addFile(__DIR__.'/.build/Help.md', 'Help.md');

echo "Comprimiendo archivo PHAR...".PHP_EOL;
$pharGZ=$phar->compress(Phar::GZ);
$pharGZ->setStub($stub);
print_r($pharGZ->getMetadata());
print_r($pharGZ->getSignature());

echo "Completado! ".(time()-$_SERVER['REQUEST_TIME']).'s';
