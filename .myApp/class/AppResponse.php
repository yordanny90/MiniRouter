<?php

class AppResponse extends \MiniRouter\Response{
	public function send(){
		if($this->get_content_type()=='text/html'){
			$content=$this->getContent();
			static::flatBuffer($this->isGz());
			include APP_DIR.'/views/index.php';
			$this->includeBuffer(true);
		}
		return parent::send();
	}
}