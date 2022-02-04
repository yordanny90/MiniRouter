<?php

namespace EndpointTask;

class hello{
	function CLI_(...$names){
		foreach($names as $n){
			echo "Hello ".$n.".".PHP_EOL;
		}
		echo "What's your name?".PHP_EOL;
		$name=readline("> ");
		echo "Hello ".$name."!";
	}

	function CLI_world(){
		echo "Hello World!!!";
	}

	function CLI_random(){
		echo "Hello ".base_convert(rand(),10,36)."!";
	}

}