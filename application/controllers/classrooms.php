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

class Classrooms extends School {
	
	/**
     * @before _secure, _school
     */
	public function add($grade_id) {
		$grade = \Grade::first(array("id = ?" => $grade_id), array("title", "id", "organization_id"));
		if (!$grade || $grade->organization_id != $this->organization->id) {
			self::redirect("/school");
		}
		$this->setSEO(array("title" => "Add Sections | School"));
		$view = $this->getActionView();

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
					"educator_id" => $teacher[$key],
					"organization_id" => $this->organization->id
				));
				$classroom->save();
			}
			$view->set("success", "Sections added successfully!");
		}
		$teachers = \Educator::all(array("organization_id = ?" => $this->organization->id), array("user_id", "id"));
		$results = array();
		foreach ($teachers as $t) {
			$alloted = \Classroom::first(array("educator_id = ?" => $t->id), array("id"));
			if ($alloted) {
				continue;
			}
			$user = \User::first(array("id = ?" => $t->user_id), array("name"));
			$results[] = array(
				"id" => $t->id,
				"name" => $user->name
			);
		}
		$results = ArrayMethods::toObject($results);

		$view->set("teachers", $results);
		$view->set("grade", $grade);
	}

	/**
	 * @before _secure, _school
	 */
	public function enrollments($classroom_id, $grade_id) {
		$classroom = \Classroom::first(array("id = ?" => $classroom_id), array("id", "organization_id", "grade_id", "educator_id"));
		if (!$classroom || $classroom->organization_id != $this->organization->id || $classroom->grade_id != $grade_id) {
			self::redirect("/school");
		}
		$this->setSEO(array("title" => "School | View students in section"));
		$view = $this->getActionView();

		$enrollments = \Enrollment::all(array("classroom_id = ?" => $classroom_id));
		$students = array();
		foreach ($enrollments as $e) {
			$student = \Scholar::first(array("id = ?" => $e->scholar_id), array("user_id", "dob", "parent_id"));
			$parent = \StudentParent::first(array("id = ?" => $student->parent_id), array("name", "relation"));
			$usr = \User::first(array("id = ?" => $student->user_id));
			$students[] = array(
				"name" => $usr->name,
				"parent_name" => $parent->name,
				"parent_relation" => $parent->relation,
				"dob" => $student->dob,
				"username" => $usr->username
			);
		}
		$students = ArrayMethods::toObject($students);
		$view->set("students", $students);
	}

	/**
     * @before _secure, _school
     */
	public function manage($grade_id) {
		$grade = \Grade::first(array("id = ?" => $grade_id), array("title", "id", "organization_id"));
		if (!$grade || $grade->organization_id != $this->organization->id) {
			self::redirect("/school");
		}
		$this->setSEO(array("title" => "Manage Sections | School"));
		$view = $this->getActionView();

		$classrooms = \Classroom::all(array("grade_id = ?" => $grade_id));

		$view->set("grade", $grade);
		$view->set("classrooms", $classrooms);
	}

	/**
	 * @before _secure, _school
	 */
	public function edit($classroom_id, $grade_id) {
		$classroom = \Classroom::first(array("id = ?" => $classroom_id));
		$grade = \Grade::first(array("id = ?" => $grade_id), array("id", "title", "organization_id"));
		if (!$classroom || $classroom->organization_id != $this->organization->id) {
			self::redirect("/school");
		}
		if (!$grade || $classroom->grade_id != $grade->id || $grade->organization_id != $this->organization->id) {
			self::redirect("/school");
		}

		$this->setSEO(array("title" => "Edit Section | School"));
		$view = $this->getActionView();

		if (RequestMethods::post("action") == "editClassroom") {
			$classroom->year = RequestMethods::post("year");
			$classroom->section = RequestMethods::post("section");
			$classroom->remarks = RequestMethods::post("remarks");
			$classroom->educator_id = RequestMethods::post("educator");
			$classroom->save();

			$view->set("success", "Grade section edited successfully!!");
		}
		$teachers = \Educator::all(array("organization_id = ?" => $this->organization->id), array("user_id", "organization_id", "id"));
		$view->set("teachers", $teachers);
		$view->set("grade", $grade);
		$view->set("classroom", $classroom);
	}
}
