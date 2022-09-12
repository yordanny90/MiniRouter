<?php
$t=time();
return [
	'time'=>$t,
	// Los datos pueden ser calculados cuando se consultan
	'fecha_ISO'=>date(DATE_RFC3339, $t),
	// O bien, pueden ser valores estáticos
	'mensaje'=>'Esto es un dataset de prueba'
];
