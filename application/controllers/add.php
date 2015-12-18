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
					"name" => Markup::sanitize($value),
					"description" => Markup::sanitize($description[$key]),
					"school_id" => $this->school->id
				));
				$grade->save();
			}

			$view->set("Success", 'Grades added successfully! Now <a href="/manage/grades">Manage Classes</a>');
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
		$this->_verifyInput("Grade", array("id = ?" => $grade_id, "school_id = ?" => $this->school->id));
		$this->setSEO(array("title" => "Admin | School | Add Courses"));
		$view = $this->getActionView();

		if (RequestMethods::post("action") == "addCourses") {
			$name = RequestMethods::post("name");
			$description = RequestMethods::post("description");

			foreach ($name as $key => $value) {
				$course = new Course(array(
					"name" => Markup::sanitize($value),
					"description" => Markup::sanitize($description[$key]),
					"grade_id" => $grade_id
				));
				$coures->save();
			}
			$view->set("Success", 'Courses add successfully! <a href="/manage/courses/'. $grade_id .'">Manage Courses</a>');
		}
	}

	/**
	 * @before _secure, _admin
	 */
	public function teachers() {
		$this->setSEO(array("title" => "Admin | School | Add Teachers"));
		$view = $this->getActionView();
	}


}