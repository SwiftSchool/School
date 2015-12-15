<?php

/**
 * The Teachers Controller
 *
 * @author Hemant Mann
 */
class Teachers extends Users {
    /**
     * @readwrite
     */
    protected $_teacher;

    protected function setTeacher($teacher) {
        $session = Registry::get("session");
        if ($teacher) {
            $session->set("teacher", $teacher);
        } else {
            $session->erase("teacher");
        }
        $this->_teacher = $teacher;
        return $this;
    }

    public function __construct($options = array()) {
        parent::__construct($options);

        $this->teacher = Registry::get("session")->get("teacher");
    }

    /**
     * @protected
     */
    public function _teacher() {
        if (!$this->teacher) {
            self::redirect("/");
        }
        $this->changeLayout();
    }

	/**
	 * @before _secure, _teacher
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

	/**
	 * @before _secure, _teacher
	 */
	public function profile() {
		$this->seo(array(
            "title" => "e-Learning",
            "keywords" => "dashboard",
            "description" => "Students/Parents Dashboard",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();

        $teacher = Registry::get("session")->get("teacher");
        $view->set("teacher", $teacher);
	}

}
