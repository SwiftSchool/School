<?php
/**
 * The Classrooms Controller
 *
 * @author Hemant Mann
 */
use Framework\RequestMethods as RequestMethods;
use Shared\Markup as Markup;
use Framework\Registry as Registry;
use Framework\ArrayMethods as ArrayMethods;

class Classrooms extends School_Admin {
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
	public function add($grade_id) {
		$grade = $this->_verifyInput("Grade", array("school_id = ?" => $this->school->id));
		$this->setSEO(array("title" => "School | Add Sections"));
		$view = $this->getActionView();

		$teachers = Teacher::all(array("school_id = ?" => $this->school->id), array("user_id", "id"));
		$results = array();
		foreach ($teachers as $t) {
			$user = User::first(array("id = ?" => $t->user_id), array("name"));
			$results[] = array(
				"id" => $t->id,
				"name" => $user->name
			);
		}
		$results = ArrayMethods::toObject($results);

		if (RequestMethods::post("action") == "addClassrooms") {
			$year = RequestMethods::post("year");
			$section = RequestMethods::post("section");
			$remarks = RequestMethods::post("remarks");
			$teacher = RequestMethods::post("teacher");

			foreach ($year as $key => $value) {
				$classroom = new \Classroom(array(
					"year" => $value,
					"grade_id" => $grade_id,
					"section" => $section[$key],
					"remarks" => $remarks[$key],
					"teacher_id" => $teacher[$key]
				));
				$classroom->save();
			}
			$view->set("success", "Sections added successfully!");
		}
		$view->set("teachers", $results);
		$view->set("grade", $grade);
	}

	/**
     * @before _secure, _admin
     */
	public function manage($grade_id) {
		$grade = $this->_verifyInput("Grade", array("school_id = ?" => $this->school->id));
		$this->setSEO(array("title" => "School | Add Sections"));
		$view = $this->getActionView();

		$classrooms = \Classroom::all(array("grade_id = ?" => $grade_id));

		$view->set("grade", $grade);
		$view->set("classrooms", $classrooms);
	}
}
