<?php

namespace Task;

use MiniRouter\RequestCLI;

class example{
	static function CLI_(){
		print_r(RequestCLI::getArgs());
	}

	static function GET_esto_no_es_cli(){ }

	static function CLI_vars(){
		print_r(RequestCLI::getArgsVars());
	}

	static function CLI_flags(){
		print_r(RequestCLI::getArgsFlags());
	}

	static function CLI_text(){
		print_r(RequestCLI::getArgsText());
	}

}