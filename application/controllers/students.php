<?php

/**
 * The Students Controller
 *
 * @author Hemant Mann
 */
use Framework\Registry as Registry;
use Framework\RequestMethods as RequestMethods;

class Students extends Auth {
    /**
     * @protected
     */
    public function _admin() {
        parent::_admin();

        $this->defaultLayout = "layouts/school";
        $this->setLayout();
    }

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

    /**
     * @before _secure, _admin
     */
    public function add() {
        $this->setSEO(array("title" => "School | Add Students"));
        $view = $this->getActionView();
        $session = Registry::get("session");

        $grades = Grade::all(array("organization_id = ?" => $session->get("school")->id));

        if (RequestMethods::post("action") == "addStudents") {
            $this->_saveUser(array("type" => "student"));
        }
        $view->set("grades", $grades);
    }

    /**
     * @before _secure, _admin
     */
    public function manage() {
        $this->setSEO(array("title" => "School | Manage Students"));
        $view = $this->getActionView();
        $session = Registry::get("session");

        $students = Student::all(array("organization_id = ?" => $session->get("school")->id), array("*"), "created", "desc", 30, 1);
        $view->set("students", $students);
    }

}
