<?php
/**
 * The Subject Controller
 *
 * @author Hemant Mann
 */
use Framework\RequestMethods as RequestMethods;
use Shared\Markup as Markup;
use Framework\Registry as Registry;
use Framework\ArrayMethods as ArrayMethods;

class Subject extends School {

	/**
	 * @before _secure, _school
	 */
	public function add($grade_id) {
		$grade = $this->_verifyInput("Grade", array("id = ?" => $grade_id, "organization_id = ?" => $this->organization->id));
		$this->setSEO(array("title" => "School | Add Courses"));
		$view = $this->getActionView();

		if (RequestMethods::post("action") == "addCourses") {
			$title = RequestMethods::post("title");
			$description = RequestMethods::post("description");

			foreach ($title as $key => $value) {
				$course = new \Course(array(
					"title" => Markup::checkValue($value),
					"description" => Markup::checkValue($description[$key]),
					"grade_id" => $grade_id
				));
				$course->save();
			}
			$view->set("success", 'Courses added successfully! <a href="/courses/manage/'. $grade_id .'">Manage Courses</a>');
		}
		$view->set("grade", $grade);
	}

	/**
	 * @before _secure, _school
	 */
	public function manage($grade_id) {
		if (!$grade_id) {
			self::redirect($this->dashboard);
		}
		$this->setSEO(array("title" => "School | Manage Subjects (Courses)"));
		$view = $this->getActionView();

		$grade = $this->_verifyInput("Grade", array("id = ?" => $grade_id, "organization_id = ?" => $this->organization->id));
		$courses = Course::all(array("grade_id = ?" => $grade_id));
		$view->set("courses", $courses);
		$view->set("grade", $grade);
	}
}