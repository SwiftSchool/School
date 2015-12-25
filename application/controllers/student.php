<?php

/**
 * The Students Controller
 *
 * @author Hemant Mann
 */
use Framework\Registry as Registry;
use Framework\RequestMethods as RequestMethods;

class Student extends Auth {
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
    protected $_dashboard = "/student/dashboard";

    /**
     * @readwrite
     */
    protected $_scholar;

    protected function setScholar($scholar) {
        $session = Registry::get("session");
        if ($scholar) {
            $session->set("scholar", $scholar);
        } else {
            $session->erase("scholar");
        }
        $this->_scholar = $scholar;
        return $this;
    }

    public function __construct($options = array()) {
        parent::__construct($options);

        $this->scholar = Registry::get("session")->get("scholar");
    }

    public function render() {
        if ($this->scholar) {
            if ($this->actionView) {
                $this->actionView->set("__student", $this->scholar);
            }

            if ($this->layoutView) {
                $this->layoutView->set("__student", $this->scholar);
            }
        }
        parent::render();
    }

    /**
     * @protected
     */
    public function _scholar() {
        if (!$this->scholar) {
            self::redirect("/");
        }
        $this->changeLayout();
    }
	/**
	 * @before _secure, _scholar
	 */
	public function index() {
		$this->setSEO(array("title" => "Students | Dashboard"));
        $view = $this->getActionView();

        $enrollment = Enrollment::first(array("scholar_id = ?" => $this->scholar->id), array("classroom_id"));
        $classroom = Classroom::first(array("id = ?" => $enrollment->classroom_id));
        $grade = Grade::first(array("id = ?" => $classroom->grade_id));
        $courses = Course::first(array("grade_id = ?" => $classroom->grade_id));

        $view->set("enrollment", $enrollment);
        $view->set("classroom", $classroom);
        $view->set("grade", $grade);
        $view->set("courses", $courses);
	}

    /**
     * @before _secure, _scholar
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
            $this->_saveUser(array("type" => "scholar"));
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

        $students = Scholar::all(array("organization_id = ?" => $session->get("school")->id), array("*"), "created", "desc", 30, 1);
        $view->set("students", $students);
    }

}
