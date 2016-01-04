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

        $students = \Scholar::all(array("organization_id = ?" => $this->organization->id), array("*"), "created", "desc", 30, 1);
        $grades = \Grade::all(array("organization_id = ?" => $this->organization->id), array("title", "id"));
        
        $setGrades = array();
        foreach ($grades as $g) {
            $setGrades["$g->id"] = $g->title;
        }
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
        if (RequestMethods::post("action") == "addToClass") {
            $classroom = Markup::checkValue(RequestMethods::post("classroom"));
            if ($classroom) {
                $enrollment = new \Enrollment(array(
                    "user_id" => $usr->id,
                    "classroom_id" => $classroom,
                    "organization_id" => $this->organization->id
                ));
                $enrollment->save();
                $view->set("success", "Student successfully added to classroom");
            } else {
                $view->set("success", "ERROR");
            }
        }

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
