<?php
/**
 * The Manage Controller
 * Controls managing of student, teachers, grade, courses etc
 *
 * @author Hemant Mann
 */
use Framework\RequestMethods as RequestMethods;
use Shared\Markup as Markup;
use Framework\Registry as Registry;
use Framework\ArrayMethods as ArrayMethods;

class Manage extends School_Admin {
	/**
	 * @protected
	 */
	public function changeLayout() {
		$this->defaultLayout = "layouts/school_admin";
		$this->setLayout();
	}

	/**
	 * @before _secure, _admin
	 */
	public function index() {

	}
	
	/**
	 * @before _secure, _admin
	 */
	public function grades() {
		$this->setSEO(array("title" => "Admin | School | Add Grades"));
		$view = $this->getActionView();

		$grades = Grade::all(array("school_id = ?" => $this->school->id));
		$view->set("grades", $grades);
	}

	/**
	 * @before _secure, _admin
	 */
	public function courses($grade_id) {
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

	/**
	 * @before _secure, _admin
	 */
	public function teachers() {
		$this->setSEO(array("title" => "Admin | School | Add Grades"));
		$view = $this->getActionView();
	}

	/**
	 * @before _secure, _admin
	 */
	public function students() {
		$this->setSEO(array("title" => "Admin | School | Add Grades"));
		$view = $this->getActionView();
	}
}
