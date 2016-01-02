<?php

/**
 * The Teachers Controller
 *
 * @author Hemant Mann
 */
use Framework\Registry as Registry;
use Framework\RequestMethods as RequestMethods;
use Framework\ArrayMethods as ArrayMethods;

class Teacher extends School {
    
    /**
     * @readwrite
     */
    protected $_educator;

    public function logout() {
        Registry::get("session")->erase("educator");
        parent::logout();
    }

    public function render() {
        if ($this->educator) {
            if ($this->actionView) {
                $this->actionView->set("educator", $this->educator);
            }

            if ($this->layoutView) {
                $this->layoutView->set("educator", $this->educator);
            }
        }

        if ($this->organization) {
            if ($this->actionView) {
                $this->actionView->set("organization", $this->organization);
            }

            if ($this->layoutView) {
                $this->layoutView->set("organization", $this->organization);
            }
        }         
        parent::render();
    }

	/**
	 * @before _secure, _teacher
	 */
	public function index() {
		$this->setSEO(array("title" => "Teachers | Dashboard"));
        $this->getLayoutView()->set("cal", true);
        $view = $this->getActionView();
	}

	/**
	 * @before _secure, _teacher
	 */
	public function profile() {
		$this->setSEO(array("title" => "Profile"));
        $view = $this->getActionView();
	}

    /**
     * @before _secure, _school
     */
    public function add() {
        $this->setSEO(array("title" => "Add Teachers"));
        $view = $this->getActionView();

        if (RequestMethods::post("action") == "addTeachers") {
            $teachers = $this->reArray($_POST);
            foreach ($teachers as $teacher) {
                $user = $this->_createUser($teacher);
                if (isset($user)) {
                    try {
                        $educator = new Educator(array(
                            "organization_id" => $this->organization->id,
                            "user_id" => $user->id,
                            "location_id" => $location->id
                        ));
                        $educator->save();
                    } catch (\Exception $e) {
                        continue;
                    }
                    
                }
            }

            $view->set("success", 'Teachers saved successfully!! See <a href="/teacher/manage">Manage Teachers');
        }
    }

    /**
     * @before _secure, _school
     */
    public function manage() {
        $this->setSEO(array("title" => "Manage Teachers"));
        $view = $this->getActionView();

        $teachers = Educator::all(array("organization_id = ?" => $this->organization->id), array("*"), "created", "desc", 30, 1);
        $view->set("teachers", $teachers);
    }

    /**
     * @before _secure, _school
     */
    public function edit($teacher_id) {
        $teacher = \Educator::first(array("id = ?" => $teacher_id), array("user_id", "organization_id"));
        if (!$teacher || $teacher->organization_id != $this->organization->id) {
            self::redirect("/school");
        }

        $this->setSEO(array("title" => "Profile"));
        $view = $this->getActionView();

        $usr = \User::first(array("id = ?" => $teacher->user_id));
        if (RequestMethods::post("action") == "editTeacher") {
            $email = RequestMethods::post("email");
            $phone = RequestMethods::post("phone");

            $emailExist = ($email != $usr->email) ? \User::first(array("email = ?" => $email), array("id")) : false;
            $phoneExist = ($phone != $usr->phone) ? \User::first(array("phone = ?" => $phone), array("id")) : false;

            if ($emailExist) {
                $view->set("error", true);
                $view->set("message", "Failed to edit the teacher! Email already exists");
            } elseif ($phoneExist) {
                $view->set("error", true);
                $view->set("message", "Phone number already exists!! Enter different phone");
            } else {
                $usr->name = RequestMethods::post("name");
                $usr->email = $email;
                $usr->phone = $phone;

                $usr->save();
                $view->set("message", "Teacher edited successfully!!");    
            }
        }

        $view->set("teacher", $usr);
    }

    /**
     * Assign which course will the teacher will teach
     * @before _secure, _school
     */
    public function assign($user_id) {
        $usr = \User::first(array("id = ?" => $user_id), array("id"));
        if (!$usr) {
            self::redirect("/school");
        }
        $this->setSEO(array("title" => "Assign Teachers for different subjects"));
        $view = $this->getActionView();

        $grades = \Grade::all(array("organization_id = ?" => $this->organization->id), array("id", "title"));
        $view->set("grades", $grades);
        if (RequestMethods::post("action") == "assignTeacher") {
            $teaches = $this->reArray($_POST);
            foreach ($teaches as $t) {
                if (!isset($t["section"]) || !isset($t["course"])) {
                    continue;
                }
                $teach = new \Teach(array(
                    "grade_id" => $t["grade"],
                    "classroom_id" => $t["section"],
                    "course_id" => $t["course"],
                    "user_id" => $usr->id,
                    "organization_id" => $this->organization->id
                ));
                $teach->save();
            }
            $view->set("success", "Subjects assigned!!");
        }
    }

    /**
     * @before _secure, _teacher
     */
    public function courses() {
        $this->setSEO(array("title" => "Profile"));
        $view = $this->getActionView();

        $teaches = \Teach::all(array("user_id = ?" => $this->educator->user_id));
        
        $result = array();
        foreach ($teaches as $t) {
            $grade = \Grade::first(array("id = ?" => $t->grade_id), array("title"));
            $class = \Classroom::first(array("id = ?" => $t->classroom_id), array("section", "year"));
            $course = \Course::first(array("id = ?" => $t->course_id), array("title"));

            $result[] = array(
                "grade" => $grade->title,
                "grade_id" => $t->grade_id,
                "section" => $class->section,
                "course" => $course->title,
                "course_id" => $t->course_id
            );
        }
        $result = ArrayMethods::toObject($result);

        $view->set("courses", $result);
    }

    /**
     * @protected
     */
    public function _teacher() {
        $this->organization = Registry::get("session")->get("organization");
        $this->educator = Registry::get("session")->get("educator");
        if (!$this->organization || !$this->educator) {
            self::redirect("/");
        }
    }

}
