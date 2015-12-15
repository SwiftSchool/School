<?php

/**
 * The Students Controller
 *
 * @author Hemant Mann
 */
use Shared\Controller as Controller;
use Framework\Registry as Registry;

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

	public function profile() {
		$this->seo(array(
            "title" => "e-Learning",
            "keywords" => "dashboard",
            "description" => "Students/Parents Dashboard",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();

        $student = Registry::get("session")->get("student");
        $view->set("student", $student);
	}

}
