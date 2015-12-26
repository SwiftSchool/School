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
     * @readwrite
     */
    protected $_scholar;

    public function __construct($options = array()) {
        parent::__construct($options);

        $this->scholar = Registry::get("session")->get("scholar");
        if (!$this->scholar) {
            self::redirect("/");
        }

        $this->defaultLayout = "layouts/student";
        $this->setLayout();
    }

    public function render() {
        if ($this->scholar) {
            if ($this->actionView) {
                $this->actionView->set("__scholar", $this->scholar);
            }

            if ($this->layoutView) {
                $this->layoutView->set("__scholar", $this->scholar);
            }
        }
        parent::render();
    }

	/**
	 * @before _secure
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
     * @before _secure
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
