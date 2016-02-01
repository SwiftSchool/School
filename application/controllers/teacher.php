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
            $storedGrades[$g->id] = $g->title;
        }
        $courses = TeacherService::$_courses;

        $result = array();
        foreach ($teaches as $t) {
            $grade = $storedGrades[$t->grade_id];
            $class = \Classroom::first(array("id = ?" => $t->classroom_id), array("id", "section", "year"));
            $course = $courses[$t->course_id];
            $asgmnt = \Assignment::count(array("course_id = ?" => $t->course_id));
            
            $data = array(
                "grade" => $grade,
                "grade_id" => $t->grade_id,
                "section" => $class->section,
                "course" => $course->title,
                "course_id" => $t->course_id,
                "classroom_id" => $class->id,
                "assignments" => $asgmnt
            );
            $data = ArrayMethods::toObject($data);
            $result[] = $data;
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

        $response = $this->_saveAttendances($classroom);
        if (isset($response["success"])) {
            $view->set("message", "Attendance Saved successfully!!");
        } elseif (isset($response["saved"])) {
            $view->set("message", "Attendance Already saved for today");
        }

        $students = $this->_findEnrollments($classroom, array('table' => 'attendance'));
        $view->set("class", $grade->title . " - ". $classroom->section);
        $view->set("students", $students);
    }

    /**
     * @todo move to services
     * Grade the students for each week
     * @before _secure, _teacher
     */
    public function weeklyStudentsPerf($course_id = null) {
        $this->setSEO(array("title" => "Grade Students Weekly Performance | Teacher"));
        $view = $this->getActionView();
        $session = Registry::get("session");

        $course_id = RequestMethods::post("course", $course_id);
        if (!$course_id) {
            if ($c = $session->get('Teacher:$courses')) {
                $teach = $c[0];
            } else {
                $teach = Teach::first(array("user_id = ?" => $this->user->id));
            }
        } else {
            $teach = Teach::first(array("course_id = ?" => $course_id, "user_id = ?" => $this->user->id));
        }

        if (!$teach) {
            self::redirect("/404");
        }

        $return = $this->_weeklyStudentsPerf($teach);
        $view->set("message", $return["message"]);

        $classroom = Classroom::first(array("id = ?" => $teach->classroom_id));
        $enrollments = $this->_findEnrollments($classroom, array('table' => 'performance', 'teach' => $teach));
        $classroom = Classroom::first(array("id = ?" => $teach->classroom_id), array("section", "grade_id"));
        $klass = Grade::first(array("id = ?" => $classroom->grade_id), array("title"));
        $course = Course::first(array("id = ?" => $teach->course_id), array("title"));

        $scale = array();
        for ($i = 1; $i <= 10; $i++) {
            $scale[] = $i;
        }

        $courses = TeacherService::$_courses;
        $view->set(array(
            "students" => $enrollments,
            "class" => $klass->title . " - " . $classroom->section,
            "course" => $course->title,
            "scale" => $scale,
            "courses" => $courses,
            "course_id" => $teach->course_id
        ));
    }

    /**
     * @todo move to Services
     * Save attendances into Mongo DB
     */
    protected function _saveAttendances(&$classroom) {
        $mongo = Registry::get("MongoDB");
        $attendance = $mongo->attendance;
        
        $date = date('Y-m-d 00:00:00');
        $mongo_date = new MongoDate(strtotime($date));
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
                    "date" => $mongo_date,
                    "presence" => (int) $a["presence"],
                    "live" => true
                );

                $where = array('user_id' => (int) $a["user_id"], 'date' => $mongo_date);
                $record = $attendance->findOne($where);
                if (isset($record)) {
                    $attendance->update($where, array('$set' => $doc));
                } else {
                    $attendance->insert($doc);
                }
            }
            return array("success" => true);
        } else {
            $record = $attendance->findOne(array('classroom_id' => (int) $classroom->id, 'date' => $mongo_date));
            if (isset($record)) {
                return array("saved" => true);
            } else {
                return array("empty" => true);
            }
        }
    }

    // @TODO move to TeacherService
    protected function _weeklyStudentsPerf($teach) {
        $mongo = Registry::get("MongoDB");
        $perf = $mongo->performance;

        if (RequestMethods::post("action") == "grade") {
            $performances = $this->reArray($_POST);

            $date = new DateTime(date('Y-m-d'));
            $week = $date->format("W");
            $yr = date('Y');

            foreach ($performances as $p) {
                if (!is_numeric($p["user_id"]) || !is_numeric($p["grade"])) {
                    return false;
                }

                $where = array('user_id' => (int) $p['user_id'], 'course_id' => (int) $teach->course_id, 'teacher_id' => (int) $this->user->id, 'year' => $yr);
                $record = $perf->findOne($where);
                
                // if record exists then we need to loop for the performance tracks
                // to update grade for this week
                $track = array();
                if (isset($record)) {
                    $found = false;
                    foreach ($record['track'] as $r) {
                        if ($week == $r['week']) {
                            $track[] = array('week' => (int) $week, 'grade' => (int) $p['grade']);
                            $found = true;
                        } else {
                            $track[] = $r;
                        }
                    }
                    if (!$found) {
                        $track[] = array('week' => (int) $week, 'grade' => (int) $p['grade']);
                    }
                    $perf->update($where, array('$set' => array('track' => $track)));
                } else {
                    $where = array_merge($where, array('classroom_id' => (int) $teach->classroom_id));
                    $track[] = array('week' => (int) $week, 'grade' => (int) $p['grade']);
                    $doc = array_merge($where, array('track' => $track));
                    $perf->insert($doc);
                }
            }
            return array('message' => 'Weekly performance of students saved!!');
        } else {
            return array('message' => null);
        }
    }

    // @TODO move function to TeacherService
    protected function _findEnrollments($classroom, $opts = array()) {
        if (isset($opts["table"])) {
            $mongo = Registry::get("MongoDB");
            $t = $mongo->$opts["table"];
        }

        if (is_array($classroom)) {
            $enrollments = array();
            foreach ($classroom as $c) {
                $e = Enrollment::all(array("classroom_id = ?" => $c->id), array("user_id", "classroom_id"));
                $enrollments = array_merge($enrollments, $e);
            }
        } else {
            $enrollments = Enrollment::all(array("classroom_id = ?" => $classroom->id), array("user_id"));    
        }

        $students = array(); $yr = date('Y');
        foreach ($enrollments as $e) {
            $usr = User::first(array("id = ?" => $e->user_id), array("name", "username"));
            if (!isset($opts['only_user'])) {
                $scholar = Scholar::first(array("user_id = ?" => $e->user_id), array("roll_no"));   
            }

            $extra = array();
            if (isset($opts["table"])) {
                switch ($opts["table"]) {
                    case 'attendance':
                        $date = date('Y-m-d 00:00:00');
                        $mongo_date = new MongoDate(strtotime($date));
                        $record = $t->findOne(array('date' => $mongo_date, 'user_id' => (int) $e->user_id));
                        if (isset($record)) {
                            $extra = array('presence' => $record["presence"]);    
                        } else {
                            $extra = array('presence' => null);
                        }
                        break;
                    
                    case 'performance':
                        $record = $t->findOne(array('user_id' => (int) $e->user_id, 'teacher_id' => (int) $this->user->id, 'course_id' => (int) ($opts["teach"]->course_id), 'year' => $yr));
                        if (isset($record)) {
                            $date = new DateTime(date('Y-m-d'));
                            $week = $date->format("W");
                            foreach ($record['track'] as $r) {
                                if ($week == $r['week']) {
                                    $extra = array('grade' => (int) $r['grade']);
                                    break;
                                }
                            }
                        }
                        break;
                }
            }

            if (isset($opts['conversation'])) {
                $klass = $classroom[$e->classroom_id];
                $extra = array(
                    'username' => $usr->username,
                    'class' => $klass->grade,
                    'section' => $klass->section,
                    'display' => $usr->name . " (Class: ". $klass->grade ." - ". $klass->section . ") Roll No: " . $scholar->roll_no
                );
            }

            if (!isset($opts['only_user'])) {
               $data = array(
                   "user_id" => $e->user_id,
                   "name" => $usr->name,
                   "roll_no" => $scholar->roll_no
               ); 
            } else {
                $data = array("user_id" => $e->user_id);
            }
            
            $data = array_merge($data, $extra);
            $data = ArrayMethods::toObject($data);
            $students[] = $data;
        }
        return $students;
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
