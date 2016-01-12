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
        parent::render();
    }

	/**
	 * @before _secure, _teacher
	 */
	public function index() {
		$this->setSEO(array("title" => "Teachers | Dashboard"));
        $this->getLayoutView()->set("cal", true);
        $view = $this->getActionView();

        $courses = Teach::all(array("user_id = ?" => $this->user->id, "live = ?" => true));
        $view->set("courses", $courses);
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
                if (!empty($t["section"]) || !empty($t["course"])) {
                    continue;
                }
                $teach = new \Teach(array(
                    "grade_id" => $t["grade"],
                    "classroom_id" => $t["section"],
                    "course_id" => $t["course"],
                    "user_id" => $usr->id,
                    "organization_id" => $this->organization->id
                ));
                if ($teach->validate()) {
                    $teach->save();
                }
            }
            $view->set("success", "Subjects assigned!!");
        }
        $teaches = Teach::all(array("user_id = ?" => $usr->id, "live = ?" => true));
        $view->set("teaches", $teaches);
    }

    /**
     * Find all the courses to which the teacher is assigned
     * @before _secure, _teacher
     */
    public function courses() {
        $this->setSEO(array("title" => "Manage Your Courses | Teacher"));
        $view = $this->getActionView();

        $teaches = \Teach::all(array("user_id = ?" => $this->educator->user_id, "live = ?" => true));
        
        $result = array();
        foreach ($teaches as $t) {
            $grade = \Grade::first(array("id = ?" => $t->grade_id), array("title"));
            $class = \Classroom::first(array("id = ?" => $t->classroom_id), array("id", "section", "year"));
            $course = \Course::first(array("id = ?" => $t->course_id), array("title"));
            $asgmnt = \Assignment::count(array("course_id = ?" => $t->course_id));
            $result[] = array(
                "grade" => $grade->title,
                "grade_id" => $t->grade_id,
                "section" => $class->section,
                "course" => $course->title,
                "course_id" => $t->course_id,
                "classroom_id" => $class->id,
                "assignments" => $asgmnt
            );
        }
        $result = ArrayMethods::toObject($result);

        $view->set("courses", $result);
    }

    /**
     * @before _secure, _school
     */
    public function removeCourse($teach_id) {
        $this->noview();

        $teach = Teach::first(array("id = ?" => $teach_id));
        if ($teach->organization_id == $this->organization->id) {
            $teach->live = false;
            // $teach->delete();
        }
        self::redirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * @before _secure, _teacher
     */
    public function manageAttendance() {
        $this->setSEO(array("title" => "Manage Your Courses | Teacher"));
        $view = $this->getActionView();

        $classroom = Classroom::first(array("educator_id = ?" => $this->educator->id), array("id", "section", "grade_id"));
        $grade = Grade::first(array("id = ?" => $classroom->grade_id), array("title"));
        $enrollments = Enrollment::all(array("classroom_id = ?" => $classroom->id), array("user_id"));

        $students = array();
        foreach ($enrollments as $e) {
            $usr = User::first(array("id = ?" => $e->user_id), array("name"));
            $scholar = Scholar::first(array("user_id = ?" => $e->user_id), array("roll_no"));

            $students[] = array(
                "user_id" => $e->user_id,
                "name" => $usr->name,
                "roll_no" => $scholar->roll_no
            );
        }
        $students = ArrayMethods::toObject($students);

        $response = $this->_saveAttendances($classroom);
        if (isset($response["success"])) {
            $view->set("message", "Attendance Saved successfully!!");
        } elseif (isset($response["saved"])) {
            $view->set("message", "Attendance Already saved for today");
        }
        $view->set("class", $grade->title . " - ". $classroom->section);
        $view->set("students", $students);
    }

    /**
     * Save attendances into Mongo DB
     */
    protected function _saveAttendances(&$classroom) {
        $mongo = Registry::get("MongoDB");
        $attendance = $mongo->attendance;
        
        if (RequestMethods::post("action") == "saveAttendance") {
            $attendances = $this->reArray($_POST);

            foreach ($attendances as $a) {
                if (!is_numeric($a["user_id"]) || !is_numeric($a["presence"])) {
                    return false;
                }
                $doc = array(
                    "user_id" => (int) $a["user_id"],
                    "classroom_id" => (int) $classroom->id,
                    "organization_id" => (int) $this->organization->id,
                    "date" => date('Y-m-d'),
                    "presence" => (int) $a["presence"],
                    "live" => true
                );

                $record = $attendance->findOne(array('user_id' => (int) $a["user_id"], 'date' => date('Y-m-d')));
                if (isset($record)) {
                    $attendance->update(array('user_id' => (int) $a["user_id"], 'date' => date('Y-m-d')), array('$set' => $doc));
                } else {
                    $attendance->insert($doc);
                }
            }
            return array("success" => true);
        } else {
            $record = $attendance->findOne(array('classroom_id' => (int) $classroom->id, 'date' => date('Y-m-d')));
            if (isset($record)) {
                return array("saved" => true);
            } else {
                return array("empty" => true);
            }
        }
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
