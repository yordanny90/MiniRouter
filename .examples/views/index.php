<?php
global $ROUTE;
?>
<html>
<head>
	<title><?=TITLE?></title>
	<base href="<?=$_SERVER['SCRIPT_NAME']?>">
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
<script type="application/javascript">
	// Previene la redirección con hashtags
	document.addEventListener('click', function(event){
		if(event.prevented) return;
		if(event.target.tagName!='A') return;
		var href=event.target.getAttribute('href');
		if(!(/^#/.exec(href))) return;
		event.preventDefault();
		window.location.hash=href;
	});
</script>
</body>
</html>