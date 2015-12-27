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
		$grade = \Grade::first(array("id = ?" => $grade_id), array("title", "id", "organization_id"));
		if (!$grade || $grade->organization_id != $this->organization->id) {
			self::redirect("/school");
		}
		$this->setSEO(array("title" => "Add Courses | School"));
		$view = $this->getActionView();

		if (RequestMethods::post("action") == "addCourses") {
			$title = RequestMethods::post("title");
			$description = RequestMethods::post("description");

			foreach ($title as $key => $value) {
				$subject_title = Markup::checkValue($value);
				if ($subject_title) {
					$course = new \Course(array(
						"title" => Markup::checkValue($value),
						"description" => Markup::checkValue($description[$key]),
						"grade_id" => $grade_id,
						"user_id" => $this->user->id,
						"organization_id" => $this->organization->id
					));
					$course->save();
				}
			}
			$view->set("success", 'Courses added successfully! <a href="/subject/manage/'. $grade_id .'">Manage Courses</a>');
		}
		$view->set("grade", $grade);
	}

	/**
	 * @before _secure, _school
	 */
	public function manage($grade_id) {
		$grade = \Grade::first(array("id = ?" => $grade_id), array("title", "id", "organization_id"));
		if (!$grade || $grade->organization_id != $this->organization->id) {
			self::redirect("/school");
		}
		$this->setSEO(array("title" => "School | Manage Subjects (Courses)"));
		$view = $this->getActionView();

		$courses = Course::all(array("grade_id = ?" => $grade_id));
		$view->set("courses", $courses);
		$view->set("grade", $grade);
	}

	/**
	 * @before _secure, _school
	 */
	public function edit($subject_id, $grade_id) {
		$course = \Course::first(array("id = ?" => $subject_id));
		if (!$course || $course->organization_id != $this->organization->id || $course->grade_id != $grade_id) {
			self::redirect("/school");
		}
		$grade = \Grade::first(array("id = ?" => $grade_id), array("id", "title", "organization_id"));

		$this->setSEO(array("title" => "School | Manage Subjects (Courses)"));
		$view = $this->getActionView();

		if (RequestMethods::post("action") == "editSubject") {
			$course->title = RequestMethods::post("title");
			$course->description = RequestMethods::post("description");

			$course->save();
			$view->set("success", "Subject Updated successfully!!");
		}
		$view->set("course", $course);
		$view->set("grade", $grade);
	}

	/**
	 * @before _secure, _school
	 */
	public function remove($subject_id, $grade_id) {
		$this->noview();
		$course = \Course::first(array("id = ?" => $subject_id));
		if (!$course || $course->organization_id != $this->organization->id || $course->grade_id != $grade_id) {
			self::redirect("/school");
		}

		$course->delete();
		self::redirect($_SERVER['HTTP_REFERER']);
	}
}