<?php

class MyRouteException extends \MiniRouter\RouteException{

	protected function responseMethodNotAllowed(){
		return AppResponse::r_text('Método no permitido. '.PHP_EOL.$this->getMessage())->httpCode($this->code);
	}

	protected function responseNotFound(){
		return AppResponse::r_text('Ruta no encontrada. '.PHP_EOL.$this->getMessage())->httpCode($this->code);
	}

	protected function responseForbidden(){
		return AppResponse::r_text('Acceso no permitido. '.PHP_EOL.$this->getMessage())->httpCode($this->code);
	}

	protected function responseExecution(){
		return AppResponse::r_text('Error de ejecución. '.PHP_EOL.$this->getMessage())->httpCode($this->code);
	}
}