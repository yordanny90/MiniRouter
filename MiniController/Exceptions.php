<?php
namespace MiniCtrl;
if(!class_exists('MiniCtrl\\MiniCtrlError')){
	class MiniCtrlError extends \ErrorException{
		public $http_code=500;

		function &http_code($http_code){
			if(is_int($http_code) && $http_code>0){
				$this->http_code=$http_code;
			}
			return $this;
		}

		function &typeBadRequest(){
			return $this->http_code(400);
		}


		function &typeUnauthorized(){
			return $this->http_code(401);
		}

		function &typeForbidden(){
			return $this->http_code(403);
		}

		function &typeNotFound(){
			return $this->http_code(404);
		}

		function &typeMethodNotAllowed(){
			return $this->http_code(405);
		}

		function &typeConflict(){
			return $this->http_code(409);
		}
	}

}