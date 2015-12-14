<?php

/**
 * The Students Controller
 *
 * @author Hemant Mann
 */
use Shared\Controller as Controller;

class Students extends Users {
	/**
	 * @before _secure, changeLayout
	 */
	public function index() {
		$this->seo(array(
            "title" => "e-Learning",
            "keywords" => "dashboard",
            "description" => "Students/Parents Dashboard",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
	}
}