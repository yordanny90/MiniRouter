<?php

use MiniRouter\Response;

$msg=(isset($exception) && is_a($exception, Exception::class)?PHP_EOL.$exception->getMessage():'');
return Response::text('Execution error.'.$msg)->http_code(500);
