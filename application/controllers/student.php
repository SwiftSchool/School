<?php

/**
 * The Students Controller
 *
 * @author Hemant Mann
 */
use Framework\Registry as Registry;
use Framework\RequestMethods as RequestMethods;
use Framework\ArrayMethods as ArrayMethods;
use Shared\Markup as Markup;

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
        $view = $this->getActionView();

        $enrollment = Enrollment::first(array("user_id = ?" => $this->user->id));
        $classroom = Classroom::first(array("id = ?" => $enrollment->classroom_id));
        $grade = Grade::first(array("id = ?" => $classroom->grade_id));
        $courses = Course::all(array("grade_id = ?" => $classroom->grade_id));

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

        $submission = \Submission::first(array("user_id = ?" => $this->user->id));
        if ($submission) {
            $view->set("success", "Assignment already submitted! Your response will be updated");
        }

        if (RequestMethods::post("action") == "submitAssignment") {
            if (RequestMethods::post("maxSize") != $maxSize) {
                $view->set("success", "Invalid Response");
                return;
            }

            $response = $this->_upload("response", array("type" => "assignments", "mimes" => "doc|docx|pdf"));
            if (!$response) {
                $view->set("success", "File Upload failed!");
                return;
            }
            if (!$submission) {
                $submission = new \Submission(array(
                    "user_id" => $this->user->id,
                    "assignment_id" => $assignment->id
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
    public function attendance() {
        $this->noview();

        $mongo = Registry::get("MongoDB");
        $attendance = $mongo->selectCollection("attendance");
        $records = $attendance->find(array('user_id' => (int) $this->user->id, 'live' => true), array('date' => true, '_id' => false, 'presence' => true));

        $i = 1; $results = array();
        foreach ($records as $r) {
            $results[] = array(
                "title" => ($r["presence"]) ? "Present" : "Absent",
                "start" => $r["date"] . "T00:00:00",
                "end" => $r["date"] . "T23:59:59",
                "allDay" => true,
                "className" => "attendance"
            );
            ++$i;
        }
        echo json_encode($results);
    }

    /**
     * @protected
     */
    public function _student() {
        $session = Registry::get("session");
        $this->organization = $session->get("organization");
        $this->scholar = $session->get("scholar");
        if (!$this->scholar) {
            self::redirect("/");
        }
    }

}
