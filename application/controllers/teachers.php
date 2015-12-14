<?php

/**
 * The Teachers Controller
 *
 * @author Hemant Mann
 */
use Shared\Controller as Controller;

class Teachers extends Users {
	/**
	 * @before _secure, changeLayout
	 */
	public function index() {
		$this->seo(array(
            "title" => "e-Learning",
            "keywords" => "dashboard",
            "description" => "Teachers Dashboard",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
	}

	public function login() {
		$this->willRenderLayoutView = false;
		$view = $this->getActionView();
		
	}
}