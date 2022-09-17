<?php
global $ROUTE;
?>
<html>
<head>
	<title><?=TITLE?></title>
	<base href="<?=APP_BASE_HREF?>">
	<meta charset="<?=ini_get('default_charset')?>">
</head>
<body>
<div>
	<a href="">Ir al inicio</a>
</div>
<pre><?php
	echo 'path: '.$ROUTE->getPath().$ROUTE->getUrlParams().PHP_EOL;
	echo ($ROUTE->getClass().'::'.$ROUTE->getFunction());
	?>
</pre>
<div><?=$content ?? ''?></div>
</body>
</html>