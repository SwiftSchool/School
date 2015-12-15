<?php

/**
 * The Students Controller
 *
 * @author Hemant Mann
 */
use Framework\Registry as Registry;

class Students extends Users {
    /**
     * @readwrite
     */
    protected $_student;

    protected function setStudent($student) {
        $session = Registry::get("session");
        if ($student) {
            $session->set("student", $student);
        } else {
            $session->erase("student");
        }
        $this->_student = $student;
        return $this;
    }

    public function __construct($options = array()) {
        parent::__construct($options);

        $this->student = Registry::get("session")->get("student");
    }

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
		$this->setSEO(array("title" => "Students | Dashboard"));
        $view = $this->getActionView();
        $view->set("student", $this->student);
	}

    /**
     * @before _secure, _student
     */
	public function profile() {
		$this->setSEO(array("title" => "Students | Profile"));
        $view = $this->getActionView();

        $view->set("student", $this->student);
	}

}
