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

}
