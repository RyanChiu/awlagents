<?php
class CaptchaComponent extends Object {
	var $Controller = null;

	function startup(&$controller)
	{
		$this->Controller = $controller;
	}

	function render()
	{
		App::import('vendor', 'kcaptcha/kcaptcha');
		$kcaptcha = new KCAPTCHA();
		$this->Controller->Session->write('captcha', $kcaptcha->getKeyString());
		exit;
	}

}
?>