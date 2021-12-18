<?php

namespace MiniRouter;

abstract class Exception extends \Exception{
	abstract public function getResponse(): Response;
}