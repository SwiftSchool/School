<?php
/**
 * The Assignments Controller
 *
 * @author Hemant Mann
 */
use Framework\RequestMethods as RequestMethods;
use Shared\Markup as Markup;
use Framework\Registry as Registry;
use Framework\ArrayMethods as ArrayMethods;

class Assignments extends Teachers {
	/**
	 * @before _secure, _teacher
	 */
	public function create() {
		$this->setSEO(array("title" => "Teacher | Create Assignments"));
		$view = $this->getActionView();

		if (RequestMethods::post("action") == "createAssign") {
			// @todo - process data
		}
	}

	/**
	 * @before _secure, _teacher
	 */
	public function manage() {
		$this->setSEO(array("title" => "Teacher | Manage Your assignments"));
		$view = $this->getActionView();

		$assignments = \Assignment::all(array("educator_id = ?" => $this->teacher->id), array("title", "created", "course_id", "classroom_id", "submission_date"));
		$results = array();
		foreach ($assignments as $a) {
			$course = \Course::first(array("id = ?" => $a->course_id), array("name", "grade_id"));
			$grade = \Grade::first(array("id = ?" => $course->grade_id), array("name"));
			$classroom = \Classroom::first(array("id = ?" => $a->classroom_id), array("section"));

			$results[] = array(
				"title" => $a->title,
				"class" => $grade->name,
				"section" => $classroom->section,
				"submission_date" => $a->submission_date,
				"created" => $a->created
			);
		}
		$results = ArrayMethods::toObject($results);
		$view->set("assignments", $results);
	}

	/**
	 * @before _secure
	 */
	public function submit($assi_id) {
		if ($this->user->type != "student") {
			self::redirect("/404");
		}
		$this->defaultLayout = "layouts/student";
		$this->setLayout();

		$assignment = \Assignment::first(array("id = ?" => $assi_id));
		if (!$assignment) {
			self::redirect("/404");
		}
		$allowed = strtotime($assignment->submission_date);
		$today = date('Y-m-d');
		if ($today < $allowed) {
			$this->setSEO(array("title" => "Teacher | Create Assignments"));
			$view = $this->getActionView();

			if (RequestMethods::post("action") == "submitAssignment") {
				$response = Markup::checkValue(RequestMethods::post("response"));
				if (!$response) {
					$view->set("error", "Invalid response!");
					return;
				}

				$submission = new \Submission(array(
					"response" => $response,
					"student_id" => Registry::get("session")->get("student")->id,
					"assignment_id" => $assignment->id
				));
				$submission->save();
				$view->set("success", "You have successfully submitted the assignment!");
			}
		} else {
			$view->set("error", "Last Date of submission is over");
		}
		
	}

	/**
	 * @before _secure, _teacher
	 */
	public function submissions($assi_id) {
		$assignment = \Assignment::first(array("id = ?" => $assi_id));
		if (!$assignment) {
			self::redirect("/404");
		}
		$this->setSEO(array("title" => "Teacher | View Assignment Submissions"));
		$view = $this->getActionView();
		
		$classroom = \Classroom::first(array("id = ?" => $assignment->classroom_id), array("section", "year", "grade_id"));
		$grade = \Grade::first(array("id = ?" => $classroom->grade_id), array("title"));

		$find = \Submission::all(array("assignment_id = ?" => $assi_id));
		$submissions = array();
		foreach ($find as $f) {
			$student = \Student::first(array("id = ?" => $f->student_id), array("user_id", "roll_no"));
			$usr = \User::first(array("id = ?" => $student->user_id), array("name"));

			$submissions[] = array(
				"student" => $usr->name,
				"student_roll_no" => $student->roll_no,
				"response" => $f->response
			);
		}
		$submissions = ArrayMethods::toObject($submissions);

		$klass = array();
		$klass["name"] = $grade->title;
		$klass["section"] = $classroom->section;
		$klass["year"] = $classroom->year;
		$klass = ArrayMethods::toObject($klass);

		$view->set("class", $klass);
		$view->set("submissions", $submissions);
		$view->set("assignment", $assignment);
	}
}
