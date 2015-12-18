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
     * Stores the dashboard redirect url
     */
    protected $_dashboard = "/students/dashboard";

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

    public function render() {
        if ($this->student) {
            if ($this->actionView) {
                $this->actionView->set("__student", $this->student);
            }

            if ($this->layoutView) {
                $this->layoutView->set("__student", $this->student);
            }
        }
        parent::render();
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
	}

    /**
     * @before _secure, _student
     */
	public function profile() {
		$this->setSEO(array("title" => "Students | Profile"));
        $view = $this->getActionView();
	}

}
