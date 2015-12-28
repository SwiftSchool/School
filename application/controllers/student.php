<?php

/**
 * The Students Controller
 *
 * @author Hemant Mann
 */
use Framework\Registry as Registry;
use Framework\RequestMethods as RequestMethods;

class Student extends School {

    /**
     * @readwrite
     */
    protected $_scholar;

    /**
     * @readwrite
     */
    protected $_organization;

    public function render() {
        if ($this->scholar) {
            if ($this->actionView) {
                $this->actionView->set("__scholar", $this->scholar);
            }

            if ($this->layoutView) {
                $this->layoutView->set("__scholar", $this->scholar);
            }
        }

        if ($this->organization) {
            if ($this->actionView) {
                $this->actionView->set("__organization", $this->organization);
            }

            if ($this->layoutView) {
                $this->layoutView->set("__organization", $this->organization);
            }
        }
        parent::render();
    }

	/**
	 * @before _secure, _student
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
     * @before _secure, _school
     */
    public function add() {
        $this->setSEO(array("title" => "Add Students"));
        $view = $this->getActionView();

        if (RequestMethods::post("action") == "addStudents") {
            $students = $this->reArray($_POST);
            foreach ($students as $student) {
                $user = $this->_createUser($student);
                if (isset($user)) {
                    $location = new \Location(array(
                        "user_id" => $user->id,
                        "address" => $student["address"],
                        "city" => $student["city"],
                        "latitude" => "",
                        "longitude" => ""
                    ));
                    $location->save();
                    $scholar = new \Scholar(array(
                        "user_id" => $user->id,
                        "dob" => $student["dob"],
                        "location_id" => $location->id,
                        "organization_id" => $this->organization->id,
                        "roll_no" => ""
                    ));
                    $scholar->save();
                }
            }
            $view->set("success", "Students have been saved successfully!!");
        }
    }

    /**
     * @before _secure, _school
     */
    public function manage() {
        $this->setSEO(array("title" => "School | Manage Students"));
        $view = $this->getActionView();
        $session = Registry::get("session");

        $students = Scholar::all(array("organization_id = ?" => $this->organization->id), array("*"), "created", "desc", 30, 1);
        $view->set("students", $students);
    }

    public function _student() {
        $this->scholar = Registry::get("session")->get("scholar");
        if (!$this->scholar) {
            self::redirect("/");
        }

        $this->defaultLayout = "layouts/student";
        $this->setLayout();
    }

}
