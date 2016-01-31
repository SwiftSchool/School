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
        $courses = Course::all(array("grade_id = ?" => $classroom->grade_id), array("title", "description", "id", "grade_id"));

        $d = StringMethods::month_se();

        // find average attendance for the month
        $attendances = $this->attendance($d['start'], $d['end'], array('return' => true));
        $present = 0; $total = 0;
        foreach ($attendances as $a) {
            $total++;
            if ($a['title'] == "Present") {
                $present++;
            }
        }
        $avg = (string) (($present / $total) * 100);

        // find total assignments
        $asmt = $this->_asgmtAPI($classroom, $courses);

        $session->set('Student:$enrollment', $enrollment)
                ->set('Student:$classroom', $classroom)
                ->set('Student:$grade', $grade)
                ->set('Student:$courses', $courses);

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

        $maxSize = "6291456";
        $view->set("maxSize", $maxSize);
        $view->set("assignment", $assignment);
        
        $allowed = strtotime($assignment->deadline);
        $today = date('Y-m-d');
        if ($today > $allowed) {
            $view->set("error", "Last Date of submission is over");
            return;
        }

        $submission = \Submission::first(array("user_id = ?" => $this->user->id, "assignment_id = ?" => $assgmt_id));
        if ($submission) {
            $view->set("success", "Assignment already submitted! Your response will be updated");
        }

        if (RequestMethods::post("action") == "submitAssignment") {
            if (RequestMethods::post("maxSize") != $maxSize) {
                $view->set("success", "Invalid Response");
                return;
            }

            $response = $this->_upload("response", array("type" => "assignments", "mimes" => "png|jpe?g|bmp|gif"));
            if (!$response) {
                $view->set("success", "File Upload failed!");
                return;
            }
            if (!$submission) {
                $submission = new \Submission(array(
                    "user_id" => $this->user->id,
                    "assignment_id" => $assignment->id,
                    "course_id" => $assignment->course_id,
                    "grade" => null,
                    "remarks" => null
                ));
            } else {
                unlink(APP_PATH ."/public/assets/uploads/assignments/". $submission->response);
            }
            $submission->response = $response;

            if (!$submission->validate()) {
                $view->set("success", "Invalid Response! Validation Failed");
                return;
            }
            $submission->save();
            $view->set("success", "You have successfully submitted the assignment!");
        }
    }

    /**
     * @before _secure, _school
     */
    public function remove($user_id) {
        $this->noview();

        $scholar = Scholar::first(array("user_id = ?" => $user_id));
        if (!$scholar || $scholar->organization_id != $this->organization->id) {
            self::redirect("/404");
        }
        $user = User::first(array("id = ?" => $user_id));
        if (!$user) {
            self::redirect("/404");   
        }
        $enrollment = Enrollment::first(array("user_id = ?" => $user->id));
        $submissions = Submission::all(array("user_id = ?" => $user->id));
        $examResults = ExamResult::all(array("user_id = ?" => $user->id));

        foreach ($examResults as $r) {
            // $r->delete();
        }
        foreach ($submissions as $s) {
            // $s->delete();
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
    public function attendance($start = null, $end = null, $opts = array()) {
        if (!isset($opts['return'])) {
            $this->noview();    
        }

        $service = new \Shared\Services\Attendance();
        $results = $service->find($start, $end);
        if (isset($opts['return'])) {
            return $results;
        }
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
        foreach ($courses as $c) {
            $a = Assignment::count(array("course_id = ?" => $c->id));
            $s = Submission::count(array("course_id = ?" => $c->id, "user_id = ?" => $this->user->id));
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

        $enrollment = !$session->get('Student:$enrollment') ? Enrollment::first(array("user_id = ?" => $this->user->id)) : $session->get('Student:$enrollment');
        $classroom = !$session->get('Student:$classroom') ? Classroom::first(array("id = ?" => $enrollment->classroom_id)) : $session->get('Student:$classroom');
        $grade = !$session->get('Student:$grade') ? Grade::first(array("id = ?" => $classroom->grade_id)) : $session->get('Student:$grade');
        $courses = !$session->get('Student:$courses') ? Course::all(array("grade_id = ?" => $classroom->grade_id), array("title", "description", "id", "grade_id")) : $session->get('Student:$courses');

        $session->set('Student:$enrollment', $enrollment)
                ->set('Student:$classroom', $classroom)
                ->set('Student:$grade', $grade)
                ->set('Student:$courses', $courses);
        if (!$this->scholar) {
            self::redirect("/");
        }
    }

    protected function _asgmtAPI($classroom, $courses) {
        $assignments = Assignment::count(array("classroom_id = ?" => $classroom->id));
        $submissions = Submission::all(array("user_id = ?" => $this->user->id));

        $setCourses = array();
        foreach ($courses as $c) {
            $setCourses[$c->id] = $c->id;
        }

        $return = array();
        $return['total'] = (int) $assignments;
        $return['submitted'] = 0;
        foreach ($submissions as $s) {
            if (in_array($s->course_id, $setCourses)) {
                $return['submitted']++;
            }
        }
        return $return;
    }
}
