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
		$counts = ArrayMethods::toObject($counts);

		$view->set("counts", $counts);
	}

	/**
	 * @before _secure, _admin
	 */
	public function addStudent() {
		$this->setSEO(array("title" => "Admin | School | Add Student"));
		$view = $this->getActionView();

		if (RequestMethods::post("action") == "addStudent") {
			$usrname = RequestMethods::post("username");
			$user = User::first(array("username = ?" => $usrname));

			if ($user) {
				$view->set("error", "Username already exists! Please choose another username");
				return;
			}

			$return = $this->_saveStudent(array("user" => null, "student" => null));
			if (isset($return["error"])) {
				$view->set("error", $return["error"]);
				return;
			}
			$view->set("success", "Student Saved Successfully");
		}
	}

	/**
	 * @before _secure, _admin
	 */
	public function addTeacher() {
		$this->setSEO(array("title" => "Admin | School | Add Teacher"));
		$view = $this->getActionView();

		if (RequestMethods::post("action") == "addTeacher") {
			$usrname = RequestMethods::post("username");
			$user = User::first(array("username = ?" => $usrname));

			if ($user) {
				$view->set("error", "Username already exists! Choose another");
				return;
			}
			$return = $this->_saveUser(null, "teacher");
			if (isset($return["error"])) {
				$view->set("error", $return["error"]);
				return;
			}

			$teacher = new Teacher(array(
				"user_id" => $return["user"]->id,
				"school_id" => $this->school->id
			));
			$teacher->save();

			$view->set("success", "Teacher Added Successfully!!");
		}
	}

	/**
	 * @before _secure, _admin
	 */
	public function manageStudents() {
		$this->setSEO(array("title" => "Admin | School | Manage Students"));
		$view = $this->getActionView();

		$students = Student::all(array("school_id = ?" => $this->school->id), array("*"), "created", "desc", 30, 1);
		$view->set("students", $students);
	}

	/**
	 * @before _secure, _admin
	 */
	public function manageTeachers() {
		$this->setSEO(array("title" => "Admin | School | Manage Teachers"));
		$view = $this->getActionView();

		$teachers = Teacher::all(array("school_id = ?" => $this->school->id), array("*"), "created", "desc", 30, 1);
		$view->set("teachers", $teachers);
	}

	protected function _saveUser($usr, $type = "student") {
		if (!$usr) {
			$usr = new User(array());
		}
		$usr->name = RequestMethods::post("name");
		$usr->username = RequestMethods::post("usrname");
		$usr->phone = RequestMethods::post("phone");
		$usr->email = RequestMethods::post("email");
		$usr->type = $type;
		$usr->password = Markup::encrypt(RequestMethods::post("password"));

		if (!$usr->validate()) {
			return array("error" => $usr->errors);
		} else {
			$usr->save();
			return array("user" => $usr);
		}

	}

	protected function _saveStudent($objs) {
		$return = $this->_saveUser($objs["user"]);
		if (isset($return["error"])) {
			return array("error" => $return["errors"]);
		}

		$student = $objs["student"];
		if (!$student) {
			$student = new Student(array());
		}
		$student->father_name = RequestMethods::post("father_name");
		$student->mother_name = RequestMethods::post("mother_name");
		$student->dob = RequestMethods::post("dob");
		$student->address = RequestMethods::post("address");
		$student->hobbies = RequestMethods::post("hobbies", "");
		$student->roll_no = RequestMethods::post("roll_no");
		$student->class = RequestMethods::post("class");
		$student->section = RequestMethods::post("section");
		$student->school_id = $this->school->id;
		$student->user_id = $return["user"]->id;

		if (!$student->validate()) {
			return array("error" => $student->errors);
		}
		$student->save();
		return array("user" => $return["user"], "student" => $student);
	}

}
