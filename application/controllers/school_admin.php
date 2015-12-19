<?php
/**
 * The School Admin Controller
 *
 * @author Hemant Mann
 */
use Framework\RequestMethods as RequestMethods;
use Shared\Markup as Markup;
use Framework\Registry as Registry;
use Framework\ArrayMethods as ArrayMethods;

class School_Admin extends Teachers {
	/**
	 * @readwrite
	 * Stores the dashboard redirect url
	 */
	protected $_dashboard = "/school_admin";

	/**
     * @protected
     */
    public function _admin() {
    	parent::_admin();
        if ($this->user->type != 'teacher') {
            self::redirect("/404");
        }

        $this->changeLayout();
    }

    /**
     * @before _secure, _admin
     */
	public function index() {
		$this->setSEO(array("title" => "Admin | School | Dashboard"));
		$view = $this->getActionView();

		$counts = array();
		$counts["students"] = Student::count(array("school_id = ?" => $this->school->id));
		$counts["teachers"] = Teacher::count(array("school_id = ?" => $this->school->id));
		$counts["classes"] = Grade::count(array("school_id = ?" => $this->school->id));
		$counts = ArrayMethods::toObject($counts);

		$session = Registry::get("session");
		$message = $session->get("redirectMessage");
		if ($message) {
			$view->set("message", $message);
			$session->erase("redirectMessage");
		}
		$view->set("counts", $counts);
	}

	/**
	 * @before _secure, _admin
	 */
	public function misc() {
		$this->setSEO(array("title" => "Admin | School | Dashboard"));
		$view = $this->getActionView();
		if (RequestMethods::post("action") == "process") {
			$opts = RequestMethods::post("opts");
			$query = $opts["query"];

			$where = array();
			foreach ($query as $q) {
				$where[$q["where"]] = $q["value"];
			}
			
			$check = $opts["model"]::all($where);
			if ($check) {
				$view->set("result", $check);
			} else {
				$view->set("error", true);
			}
		}
		
	}

	/**
	 * @before _secure, _admin
	 */
	public function addStudents() {
		$this->setSEO(array("title" => "Admin | School | Add Students"));
		$view = $this->getActionView();

		$grades = Grade::all(array("school_id = ?" => $this->school->id));

		if (RequestMethods::post("action") == "addStudents") {
			$this->_saveUser(array("type" => "student"));
		}
		$view->set("grades", $grades);
	}

	public function manageStudents() {
		$this->setSEO(array("title" => "Admin | School | Manage Students"));
		$view = $this->getActionView();

		$students = Student::all(array("school_id = ?" => $this->school->id), array("*"), "created", "desc", 30, 1);
		$view->set("students", $students);
	}

	/**
	 * @before _secure, _admin
	 */
	public function addTeachers() {
		$this->setSEO(array("title" => "Admin | School | Add Teachers"));
		$view = $this->getActionView();

		if (RequestMethods::post("action") == "addTeachers") {
			$this->_saveUser(array("type" => "teacher"));

			$view->set("success", 'Teachers saved successfully!! Go to <a href="/manage/teachers">Manage Teachers');
		}
	}

	public function manageTeachers() {
		$this->setSEO(array("title" => "Admin | School | Manage Teachers"));
		$view = $this->getActionView();

		$teachers = Teacher::all(array("school_id = ?" => $this->school->id), array("*"), "created", "desc", 30, 1);
		$view->set("teachers", $teachers);
	}

	public function addCourses($grade_id) {
		$grade = $this->_verifyInput("Grade", array("id = ?" => $grade_id, "school_id = ?" => $this->school->id));
		$this->setSEO(array("title" => "Admin | School | Add Courses"));
		$view = $this->getActionView();

		if (RequestMethods::post("action") == "addCourses") {
			$title = RequestMethods::post("title");
			$description = RequestMethods::post("description");
			$code = RequestMethods::post("code");

			foreach ($title as $key => $value) {
				$course = new Course(array(
					"title" => Markup::sanitize($value),
					"description" => Markup::sanitize($description[$key]),
					"code" => Markup::sanitize($code[$key]),
					"grade_id" => $grade_id
				));
				$course->save();
			}
			$view->set("success", 'Courses added successfully! <a href="/manage/courses/'. $grade_id .'">Manage Courses</a>');
		}
		$view->set("grade", $grade);
	}

	public function manageCourses($grade_id) {
		if (!$grade_id) {
			self::redirect($this->dashboard);
		}
		$this->setSEO(array("title" => "Admin | School | Add Grades"));
		$view = $this->getActionView();

		$grade = $this->_verifyInput("Grade", array("id = ?" => $grade_id, "school_id = ?" => $this->school->id));
		$courses = Course::all(array("grade_id = ?" => $grade_id));
		if (!$courses) {
			Registry::get("session")->set("redirectMessage", "No courses to display");
			self::redirect($this->dashboard);
		}
		$view->set("courses", $courses);
		$view->set("grade", $grade);
	}

	public function addGrades() {
		$this->setSEO(array("title" => "Admin | School | Add Grades"));
		$view = $this->getActionView();

		if (RequestMethods::post("action") == "addGrades") {
			$name = RequestMethods::post("name");
			$description = RequestMethods::post("description");

			foreach ($name as $key => $value) {
				$grade = new Grade(array(
					"title" => Markup::sanitize($value),
					"description" => Markup::sanitize($description[$key]),
					"school_id" => $this->school->id
				));
				$grade->save();
			}

			$view->set("success", 'Classes added successfully! Now <a href="/manage/grades">Manage Classes</a>');
		}
	}

	public function manageGrades() {
		$this->setSEO(array("title" => "Admin | School | Add Grades"));
		$view = $this->getActionView();

		$grades = Grade::all(array("school_id = ?" => $this->school->id));
		$view->set("grades", $grades);
	}

	protected function _saveUser($opts) {
		$name = RequestMethods::post("name");
		$email = RequestMethods::post("email");
		$phone = RequestMethods::post("phone");

		if ($opts["type"] == "student") {
			$dob = RequestMethods::post("dob");
			$address = RequestMethods::post("address");
			$parentName = RequestMethods::post("parent");
			$relation = RequestMethods::post("relation");
			$parentPhone = RequestMethods::post("parent_phone");
		}

		$last = \User::first(array(), array("id", "created"), "created", "desc");
		$id = $last->id;
		$prefix = strtolower(array_shift(explode(" ", $this->school->name)));
		foreach ($name as $key => $value) {
			$user = new \User(array(
				"name" => $value,
				"email" => $email[$key],
				"phone" => $phone[$key],
				"username" => $prefix. "_" .(++$id),
				"password" => Markup::encrypt("password"),
				"type" => $opts["type"]
			));
			$user->save();

			if ($opts["type"] == "teacher") {
				$teacher = new \Teacher(array(
					"user_id" => $user->id,
					"school_id" => $this->school->id
				));
				$teacher->save();
			} elseif ($opts["type"] == "student") {
				$parent = new \StudentParent(array(
					"relation" => $relation[$key],
					"phone" => $parentPhone[$key],
					"name" => $parentName[$key]
				));
				$parent->save();

				$student = new \Student(array(
					"dob" => $dob[$key],
					"parent_id" => $parent->id,
					"address" => $address[$key],
					"school_id" => $this->school->id,
					"roll_no" => "",
					"user_id" => $user->id
				));
				$student->save();
			}
			
		}
	}


}
