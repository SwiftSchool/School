<?php

/**
 * The Teachers Controller
 *
 * @author Hemant Mann
 */
use Framework\Registry as Registry;
use Framework\RequestMethods as RequestMethods;
use Framework\ArrayMethods as ArrayMethods;
use Shared\Services\Teacher as TeacherService;

class Teacher extends School {
    
    /**
     * @readwrite
     */
    protected $_educator;

    public function logout() {
        Registry::get("session")->erase("educator");
        TeacherService::destroy();
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

        $courses = TeacherService::$_courses;
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

        $teaches = \Teach::all(array("user_id = ?" => $this->user->id, "live = ?" => true));
        $grades = \Grade::all(array("organization_id = ?" => $this->organization->id), array("id", "title"));
        
        $storedGrades = array();
        foreach ($grades as $g) {
            $storedGrades[$g->id] = $g;
        }
        $courses = TeacherService::$_courses;
        $classes = TeacherService::$_classes;

        $result = array();
        foreach ($teaches as $t) {
            $grade = $storedGrades[$t->grade_id];
            $class = $classes[$t->classroom_id];
            $course = $courses[$t->course_id];
            $asgmnt = \Assignment::count(array("course_id = ?" => $t->course_id));
            
            $data = array(
                "grade" => $grade->title,
                "grade_id" => $g->id,
                "section" => $class->section,
                "course" => $course->title,
                "course_id" => $course->id,
                "classroom_id" => $class->id,
                "assignments" => $asgmnt
            );
            $data = ArrayMethods::toObject($data);
            $result[] = $data;
        }
        $session = Registry::get("session");
        if ($session->get('Notification\Students:$sent')) {
            $view->set("success", "Notification sent to students");
            $session->erase('Notification\Students:$sent');
        }

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
        $service = new Shared\Services\Classroom();

        $response = $service->saveAttendance($classroom);
        if (isset($response["success"])) {
            $view->set("message", "Attendance Saved successfully!!");
        } elseif (isset($response["saved"])) {
            $view->set("message", "Attendance Already saved for today");
        }
        
        $students = $service->enrollments($classroom, array('table' => 'attendance'));
        $view->set("class", $grade->title . " - ". $classroom->section);
        $view->set("students", $students);
    }

    /**
     * @todo move to services
     * Grade the students for each week
     * @before _secure, _teacher
     */
    public function weeklyStudentsPerf($course_id, $classroom_id) {
        $this->setSEO(array("title" => "Grade Students Weekly Performance | Teacher"));
        $view = $this->getActionView();
        $session = Registry::get("session");

        if (!$course_id || !$classroom_id) {
            self::redirect("/404");
        }
        $teach = Teach::first(array("course_id = ?" => $course_id, "classroom_id = ?" => $classroom_id, "user_id = ?" => $this->user->id));

        if (!$teach) {
            self::redirect("/404");
        }

        $service = new Shared\Services\Classroom();
        $return = $service->weeklyPerformance($teach);
        $view->set($return);

        $classroom = TeacherService::$_classes[$teach->classroom_id];
        $course = TeacherService::$_courses[$teach->course_id];

        $enrollments = $service->enrollments($classroom, array('table' => 'performance', 'teach' => $teach));

        $scale = array();
        for ($i = 1; $i <= 10; $i++) {
            $scale[] = $i;
        }

        $view->set(array(
            "students" => $enrollments,
            "class" => $classroom->grade . " - " . $classroom->section,
            "course" => $course->title,
            "scale" => $scale
        ));
    }

    /**
     * @protected
     */
    public function _teacher() {
        $session = Registry::get("session");
        $this->organization = $session->get("organization");
        $this->educator = $session->get("educator");
        TeacherService::init($this->educator);

        if (!$this->organization || !$this->educator) {
            self::redirect("/");
        }
    }
}
