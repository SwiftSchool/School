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
	 * @protected
	 */
	public function changeLayout() {
		$this->defaultLayout = "layouts/school";
		$this->setLayout();
	}
	
	/**
     * @before _secure, _admin
     */
	public function add($grade_id) {
		$grade = $this->_verifyInput("Grade", array("organization_id = ?" => $this->school->id, "id = ?" => $grade_id));
		$this->setSEO(array("title" => "School | Add Sections"));
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
					"educator_id" => $teacher[$key]
				));
				$classroom->save();
			}
			$view->set("success", "Sections added successfully!");
		}
		$teachers = \Educator::all(array("organization_id = ?" => $this->school->id), array("user_id", "id"));
		$results = array();
		foreach ($teachers as $t) {
			$alloted = \Classroom::first(array("educator_id = ?" => $t->id));
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
	 * @before _secure, _admin
	 */
	public function enrollments($classroom_id, $grade_id) {
		$grade = $this->_verifyInput("Grade", array("organization_id = ?" => $this->school->id));
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
     * @before _secure, _admin
     */
	public function manage($grade_id) {
		$grade = $this->_verifyInput("Grade", array("organization_id = ?" => $this->school->id, "id = ?" => $grade_id));
		$this->setSEO(array("title" => "School | Add Sections"));
		$view = $this->getActionView();

		$classrooms = \Classroom::all(array("grade_id = ?" => $grade_id));

		$view->set("grade", $grade);
		$view->set("classrooms", $classrooms);
	}
}
