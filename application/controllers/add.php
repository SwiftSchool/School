<?php
/**
 * The Add Controller
 * Controls adding of anything student, teachers, grade, courses etc
 *
 * @author Hemant Mann
 */
use Framework\RequestMethods as RequestMethods;
use Shared\Markup as Markup;
use Framework\Registry as Registry;
use Framework\ArrayMethods as ArrayMethods;

class Add extends School_Admin {
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
	public function grades() {
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

	/**
	 * @before _secure, _admin
	 */
	public function students() {
		$this->setSEO(array("title" => "Admin | School | Add Students"));
		$view = $this->getActionView();
	}

	/**
	 * @before _secure, _admin
	 */
	public function courses($grade_id) {
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

	/**
	 * @before _secure, _admin
	 */
	public function teachers() {
		$this->setSEO(array("title" => "Admin | School | Add Teachers"));
		$view = $this->getActionView();
	}


}