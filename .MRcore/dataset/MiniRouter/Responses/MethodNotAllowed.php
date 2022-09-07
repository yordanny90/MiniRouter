<?php

use MiniRouter\Response;

$msg=(isset($exception) && is_a($exception, Exception::class)?PHP_EOL.$exception->getMessage():'');
return Response::text('Method not allowed.'.$msg)->http_code(405);