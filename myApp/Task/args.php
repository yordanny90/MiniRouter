<?php
namespace Task;
use MiniRouter\RequestCLI;

class args{
	function CLI_(){
		print_r(RequestCLI::getArgs());
	}

	function CLI_vars(){
		print_r(RequestCLI::getArgsVars());
	}

	function CLI_flags(){
		print_r(RequestCLI::getArgsFlags());
	}

	function CLI_text(){
		print_r(RequestCLI::getArgsText());
	}

}