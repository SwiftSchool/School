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
     * @readwrite
     */
    protected $_student = false;

    /**
     * @protected
     */
    public function _student() {
        if (!$this->student) {
            self::redirect("/");
        }
        $this->changeLayout();
    }
	/**
	 * @before _secure, _student
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

    /**
     * @before _secure, _student
     */
	public function profile() {
		$this->seo(array(
            "title" => "e-Learning",
            "keywords" => "dashboard",
            "description" => "Students/Parents Dashboard",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();

        $view->set("student", $this->student);
	}

}
