<?php

/**
 * The Students Controller
 *
 * @author Hemant Mann
 */
use Framework\Registry as Registry;
use Framework\RequestMethods as RequestMethods;
use Framework\ArrayMethods as ArrayMethods;
use Framework\StringMethods as StringMethods;
use Shared\Markup as Markup;
use Shared\Services\Student as StudentService;

class Student extends School {

    /**
     * @readwrite
     */
    protected $_scholar;

    public function logout() {
        Registry::get("session")->erase("scholar");
        StudentService::destroy();
        parent::logout();
    }

    public function render() {
        if ($this->scholar) {
            if ($this->actionView) {
                $this->actionView->set("scholar", $this->scholar);
            }

            if ($this->layoutView) {
                $this->layoutView->set("scholar", $this->scholar);
            }
        }
        parent::render();
    }

	/**
	 * @before _secure, _student
	 */
	public function index() {
		$this->setSEO(array("title" => "Students | Dashboard"));
        $this->getLayoutView()->set("cal", true);
        $view = $this->getActionView();
        $session = Registry::get("session");

        $enrollment = Enrollment::first(array("user_id = ?" => $this->user->id));
        $classroom = Classroom::first(array("id = ?" => $enrollment->classroom_id));
        $grade = Grade::first(array("id = ?" => $classroom->grade_id));
        $courses = StudentService::$_courses;

        $d = StringMethods::month_se();

        // find average attendance for the month
        $service = new \Shared\Services\Attendance();
        $attendances = $service->find($start, $end);
        $present = 0; $total = 0;
        foreach ($attendances as $a) {
            $total++;
            if ($a['title'] == "Present") {
                $present++;
            }
        }
        $avg = (string) (($present / $total) * 100);

        // find total assignments
        $service = new \Shared\Services\Assignment();
        $asmt = $service->total($classroom, $courses);

        $view->set("enrollment", $enrollment)
            ->set("classroom", $classroom)
            ->set("grade", $grade)
            ->set("courses", $courses)
            ->set("assignments", $asmt)
            ->set("attendance", substr($avg, 0, 5));
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

        $grades = \Grade::all(array("organization_id = ?" => $this->organization->id), array("title", "id"));
        if (RequestMethods::post("action") == "findStudents") {
            $classroom_id = RequestMethods::post("classroom_id");
            $enrollment = \Enrollment::all(array("classroom_id = ?" => $classroom_id));

            $students = array();
            foreach ($enrollment as $e) {
                $s = Scholar::first(array("user_id = ?" => $e->user_id));
                $students[] = $s;
            }
        } else {
            $students = \Scholar::all(array("organization_id = ?" => $this->organization->id), array("*"), "created", "desc", 30, 1);
        }
        $setGrades = array();
        foreach ($grades as $g) {
            $setGrades["$g->id"] = $g->title;
        }
        
        $view->set("defGrades", $grades);
        $view->set("grades", $setGrades);
        $view->set("students", $students);
    }

    /**
     * @before _secure, _school
     */
    public function addGuardian($scholar_user_id) {
        $usr = \User::first(array("id = ?" => $scholar_user_id), array("id"));
        if (!$usr) {
            self::redirect("/school");
        }
        $this->setSEO(array("title" => "Parent Info | Student | School"));
        $view = $this->getActionView();

        if (RequestMethods::post("action") == "saveParent") {
            $opts = array();
            $opts["name"] = RequestMethods::post("name");
            $opts["email"] = RequestMethods::post("email");
            $opts["phone"] = RequestMethods::post("phone");

            try {
                $user = $this->_createUser($opts);
                $loc = new Location(array(
                    "address" => RequestMethods::post("address"),
                    "city" => RequestMethods::post("city"),
                    "latitude" => "",
                    "longitude" => "",
                    "user_id" => $user->id
                ));
                $loc->save();
                $guardian = new Guardian(array(
                    "user_id" => $user->id,
                    "scholar_user_id" => $scholar_user_id,
                    "relation" => RequestMethods::post("relation"),
                    "occupation" => RequestMethods::post("occupation"),
                    "qualification" => RequestMethods::post("qualification"),
                    "location_id" => $loc->id
                ));
                $guardian->save();

                $view->set("success", $guardian->relation . " info saved successfully!!");
            } catch (\Exception $e) {
                $view->set("error", true);
                $view->set("message", $e->getMessage());
            }
        }
    }

    /**
     * @before _secure, _school
     */
    public function addToClass($user_id) {
        $usr = \User::first(array("id = ?" => $user_id), array("id"));
        if (!$usr) {
            self::redirect("/school");
        }
        $this->setSEO(array("title" => "Parent Info | Student | School"));
        $view = $this->getActionView();

        $grades = \Grade::all(array("organization_id = ?" => $this->organization->id), array("id", "title"));
        $enrollment = Enrollment::first(array("user_id = ?" => $user_id));
        if ($enrollment) {
            $view->set("success", "Student has already been added! Response will be updated");
        }
        if (RequestMethods::post("action") == "addToClass") {
            $classroom = Markup::checkValue(RequestMethods::post("classroom"));
            if (!$enrollment) {
                $enrollment = new Enrollment(array());
            }
            $enrollment->user_id = $usr->id;
            $enrollment->classroom_id = $classroom;
            $enrollment->organization_id = $this->organization->id;
            
            if ($enrollment->validate()) {
                $enrollment->save();
                $view->set("success", "Student successfully added to classroom");
            }
        }
        if ($enrollment) {
            $class = Classroom::first(array("id = ?" => $enrollment->classroom_id), array("id", "grade_id", "section"));
            foreach ($grades as $g) {
                if ($g->id == $class->grade_id) {
                    $grade = $g->id;
                    break;
                }
            }
            $view->set("class", $class);
        }
        if (!isset($grade)) {
            $grade = null;
        }

        $view->set("grade", $grade);
        $view->set("enrollment", $enrollment);
        $view->set("grades", $grades);
    }

    /**
     * Finds assignments for the classroom
     *
     * @param int $course_id Finds assignments only for the given course (Optional)
     * else finds all assignments for the student's classroom
     * @before _secure, _student
     */
    public function assignments($course_id = null) {
        $this->setSEO(array("title" => "Assignments | Student"));
        $view = $this->getActionView();
        $session = Registry::get("session");

        $classroom = StudentService::$_classroom;
        $courses = StudentService::$_courses;

        $course_id = RequestMethods::post("course", $course_id);
        if ($course_id) {
            $assignments = \Assignment::all(array("course_id = ?" => $course_id, "live = ?" => true));
        } else {
            $assignments = \Assignment::all(array("classroom_id = ?" => $classroom->id, "live = ?" => true));
        }

        $service = new Shared\Services\Assignment();
        $result = $service->all($assignments, $courses);
        $view->set("assignments", $result)
            ->set("courses", $courses);
    }

    /**
     * @before _secure, _student
     */
    public function submitAssignment($assgmt_id) {
        $assignment = \Assignment::first(array("id = ?" => $assgmt_id));
        if (!$assignment || $assignment->organization_id != $this->organization->id) {
            self::redirect("/404");
        }
        $this->setSEO(array("title" => "Submit Assignment"));
        $view = $this->getActionView();

        $service = new \Shared\Services\Assignment();
        $params = $service->submit();
        $view->set($params);
    }

    /**
     * @before _secure, _school
     */
    public function remove($user_id) {
        $this->noview();
        $sub = Registry::get("MongoDB")->submission;

        $scholar = Scholar::first(array("user_id = ?" => $user_id));
        if (!$scholar || $scholar->organization_id != $this->organization->id) {
            self::redirect("/404");
        }
        $user = User::first(array("id = ?" => $user_id));
        if (!$user) {
            self::redirect("/404");   
        }
        $enrollment = Enrollment::first(array("user_id = ?" => $user->id));
        $submissions = $sub->find(array("user_id" => (int) $user->id));
        $examResults = ExamResult::all(array("user_id = ?" => $user->id));

        foreach ($examResults as $r) {
            // $r->delete();
        }
        foreach ($submissions as $s) {
            // $s->remove();
        }
        // $enrollment->delete();
        // $user->delete();
        // $scholar->delete();

        self::redirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * @before _secure, _school
     */
    public function edit($user_id) {
        $this->setSEO(array("title" => "Edit Student Info | School"));
        $view = $this->getActionView();

        $user = User::first(array("id = ?" => $user_id));
        $scholar = Scholar::first(array("user_id = ?" => $user->id));
        if (!$scholar || $scholar->organization_id != $this->organization->id) {
            self::redirect("/404");
        }
        $location = Location::first(array("user_id = ?" => $user->id));

        if (RequestMethods::post("action") == "updateInfo") {
            $user->name = RequestMethods::post("name");
            $user->email = RequestMethods::post("email");
            $user->phone = RequestMethods::post("phone");
            $user->save();

            $location->address = RequestMethods::post("address");
            $location->city = RequestMethods::post("city");
            if ($location->validate()) {
                $location->save();
            }

            $scholar->dob = RequestMethods::post("dob");
            $scholar->roll_no = RequestMethods::post("roll_no");
            if ($scholar->validate()) {
                $scholar->save();
            }

            $view->set("success", "Saved successfully!!");
        }

        $view->set("usr", $user);
        $view->set("scholar", $scholar);
        $view->set("location", $location);
    }

    /**
     * @before _secure, _student
     */
    public function attendance($start = null, $end = null) {
        $this->noview();

        $service = new \Shared\Services\Attendance();
        $results = $service->find($start, $end);
        
        echo json_encode($results);
    }

    /**
     * @before _secure, _student
     */
    public function attendances() {
        $this->setSEO(array("title" => "Attendance | Student"));
        $view = $this->getActionView();
        $this->getLayoutView()->set("cal", true);
    }

    /**
     * @before _secure, _student
     */
    public function result($course_id = null) {
        $this->setSEO(array("title" => "Result | Student"));
        $view = $this->getActionView();
        
        $course_id = RequestMethods::post("course", $course_id);
        $courses = StudentService::$_courses;
        if (!$course_id) {
            $course = array_shift($courses);
        } else {
            $course = $courses[$course_id];
        }

        $service = new StudentService();
        $result = $service->results($course);

        $view->set("subject", $course->title)
            ->set("results", $result)
            ->set("courses", $courses);
    }

    /**
     * @before _secure, _student
     */
    public function courses() {
        $this->setSEO(array("title" => "Result | Student"));
        $view = $this->getActionView();
        $session = Registry::get("session");

        $courses = StudentService::$_courses;
        $result = array();
        $sub = Registry::get("MongoDB")->submission;
        foreach ($courses as $c) {
            $a = Assignment::count(array("course_id = ?" => $c->id));
            $s = $sub->count(array("course_id" => (int) $c->id, "user_id" => (int) $this->user->id));
            $data = array(
                "_title" => $c->title,
                "_description" => $c->description,
                "_grade_id" => $c->grade_id,
                "_id" => $c->id,
                "_assignments" => $a,
                "_assignment_submitted" => $s
            );
            $data = ArrayMethods::toObject($data);
            $result[] = $data;
        }
        $view->set("courses", $result);
    }

    /**
     * @before _secure, _student
     */
    public function performance($course_id = null) {
        $this->JSONView();
        $view = $this->getActionView();

        $course_id = RequestMethods::post("course", $course_id);
        $courses = StudentService::$_courses;
        if (!$course_id) {
            $course = array_shift($courses);
        } else {
            $course = $courses[$course_id];
        }

        $service = new StudentService();
        $find = $service->performance($course);

        $view->set("performance", $find['performance'])
            ->set("monthly", $find['monthly']);
    }

    /**
     * @protected
     */
    public function _student() {
        $session = Registry::get("session");
        $this->organization = $session->get("organization");
        $this->scholar = $session->get("scholar");
        StudentService::init($this->scholar);

        
        if (!$this->scholar) {
            self::redirect("/");
        }
    }
}
