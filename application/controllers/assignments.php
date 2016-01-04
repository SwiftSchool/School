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

class Assignments extends Teacher {
	/**
	 * @before _secure, _teacher
	 */
	public function create($course_id, $classroom_id) {
		$teach = \Teach::first(array("course_id = ?" => $course_id, "user_id = ?" => $this->user->id));
		if (!$teach || $teach->classroom_id != $classroom_id) {
			self::redirect("/teacher");
		}

		$this->setSEO(array("title" => "Teacher | Create Assignments"));
		$view = $this->getActionView();

		$grade = Grade::first(array("id = ?" => $teach->grade_id), array("title", "id"));
		$classroom = Classroom::first(array("id = ?" => $classroom_id), array("section", "id"));

		$view->set("grade", $grade);
		$view->set("classroom", $classroom);
		
		if (RequestMethods::post("action") == "assignment") {
			$assignment = new \Assignment(array(
				"title" => RequestMethods::post("title"),
				"description" => RequestMethods::post("description"),
				"deadline" => RequestMethods::post("deadline"),
				"user_id" => $this->user->id,
				"organization_id" => $this->organization->id,
				"classroom_id" => $classroom_id,
				"course_id" => $course_id
			));

			if (!$assignment->validate()) {
				$view->set("success", "Invalid Request");
				return;
			}
			$assignment->save();
			$view->set("success", "Assignment Saved successfully!!");
		}
	}

	/**
	 * @before _secure, _teacher
	 */
	public function manage() {
		$this->setSEO(array("title" => "Manage Your assignments | Teacher"));
		$view = $this->getActionView();

		$assignments = \Assignment::all(array("user_id = ?" => $this->user->id), array("title", "created", "course_id", "classroom_id", "deadline", "live", "id"));
		$results = array();
		foreach ($assignments as $a) {
			$course = \Course::first(array("id = ?" => $a->course_id), array("title", "grade_id"));
			$grade = \Grade::first(array("id = ?" => $course->grade_id), array("title"));
			$classroom = \Classroom::first(array("id = ?" => $a->classroom_id), array("section"));

			$results[] = array(
				"id" => $a->id,
				"title" => $a->title,
				"class" => $grade->title,
				"live" => $a->live,
				"section" => $classroom->section,
				"deadline" => $a->deadline,
				"created" => $a->created,
				"course_id" => $a->course_id,
				"classroom_id" => $a->classroom_id
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
					"scholar_id" => Registry::get("session")->get("student")->id,
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
		if (!$assignment || $assignment->user_id != $this->user->id) {
			self::redirect("/404");
		}
		$this->setSEO(array("title" => "View Assignment Submissions | Teacher"));
		$view = $this->getActionView();
		
		$classroom = \Classroom::first(array("id = ?" => $assignment->classroom_id), array("section", "year", "grade_id"));
		$grade = \Grade::first(array("id = ?" => $classroom->grade_id), array("title"));

		$find = \Submission::all(array("assignment_id = ?" => $assi_id));
		$submissions = array();
		foreach ($find as $f) {
			$usr = \User::first(array("id = ?" => $f->user_id), array("name"));
			$student = \Scholar::first(array("user_id = ?" => $f->user_id), array("roll_no"));

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
