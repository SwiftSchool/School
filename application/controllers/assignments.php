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
			$headers = getallheaders();
			$attachment = $this->_upload("attachment", "assignments");
			
			$assignment = new \Assignment(array(
				"title" => RequestMethods::post("title"),
				"description" => RequestMethods::post("description"),
				"deadline" => RequestMethods::post("deadline"),
				"user_id" => $this->user->id,
				"organization_id" => $this->organization->id,
				"classroom_id" => $classroom_id,
				"course_id" => $course_id,
				"attachment" => $attachment
			));

			if (!$assignment->validate()) {
				$view->set("success", "Invalid Request");
				return;
			}
			$assignment->save();
			$view->set("success", "Assignment Created successfully!!");
		}
	}

	/**
	 * @before _secure, _teacher
	 */
	public function manage($course_id = null) {
		$this->setSEO(array("title" => "Manage Your assignments | Teacher"));
		$view = $this->getActionView();

		$where = array("user_id = ?" => $this->user->id);
		$fields = array("title", "created", "course_id", "classroom_id", "deadline", "live", "id");

		$course_id = RequestMethods::post("course", $course_id);
		if ($course_id) {
			$assignments = \Assignment::all(array_merge($where, array("course_id = ?" => $course_id)), $fields);
		} else {
			$assignments = \Assignment::all($where, $fields);
		}
		$results = array();

		$grades = Grade::all(array("organization_id = ?" => $this->organization->id), array("id", "title"));
		$storedGrades = array();
		foreach ($grades as $g) {
			$storedGrades[$g->id] = $g->title;
		}

		$courses = $this->_courses();
		$classrooms = array();
		$message = Registry::get("session")->get('$redirectMessage');
		if ($message) {
			$view->set("message", $message);
			Registry::get("session")->erase('$redirectMessage');
		}
		$notification = Registry::get("MongoDB")->notifications;
		foreach ($assignments as $a) {
			$course = $courses[$a->course_id];
			$grade = $storedGrades[$course->grade_id];

			if (!isset($classrooms[$a->classroom_id])) {
				$classroom = $classrooms[$a->classroom_id] = \Classroom::first(array("id = ?" => $a->classroom_id), array("section"));
			} else {
				$classroom = $classrooms[$a->classroom_id];
			}
			$record = $notification->findOne(array('sender' => 'user', 'sender_id' => (int) $this->user->id, 'type' => 'assignment', 'type_id' => (int) $a->id));
			if (isset($record)) {
				$notify = false;
			} else {
				$notify = true;
			}

			$data = array(
				"id" => $a->id,
				"course" => $course->title,
				"title" => $a->title,
				"class" => $grade,
				"notified" => !$notify,
				"live" => $a->live,
				"section" => $classroom->section,
				"deadline" => $a->deadline,
				"created" => $a->created,
				"course_id" => $a->course_id,
				"classroom_id" => $a->classroom_id
			);
			$data = ArrayMethods::toObject($data);
			$results[] = $data;
		}
		$view->set("assignments", $results)
			->set("courses", $courses)
			->set("course_id", $course_id);
	}

	/**
	 * @before _secure, _teacher
	 */
	public function submissions($assgmt_id) {
		$assignment = \Assignment::first(array("id = ?" => $assgmt_id));
		if (!$assignment || $assignment->user_id != $this->user->id) {
			self::redirect("/404");
		}
		$this->setSEO(array("title" => "View Assignment Submissions | Teacher"));
		$view = $this->getActionView();
		
		$classroom = \Classroom::first(array("id = ?" => $assignment->classroom_id), array("section", "year", "grade_id"));
		$grade = \Grade::first(array("id = ?" => $classroom->grade_id), array("title"));

		$find = \Submission::all(array("assignment_id = ?" => $assgmt_id));
		$submissions = array();
		foreach ($find as $f) {
			$usr = \User::first(array("id = ?" => $f->user_id), array("name"));
			$student = \Scholar::first(array("user_id = ?" => $f->user_id), array("roll_no"));

			$submissions[] = array(
				"student" => $usr->name,
				"submission_id" => $f->id,
				"student_roll_no" => $student->roll_no,
				"response" => $f->response,
				"live" => $f->live,
				"submitted_on" => $f->created
			);
		}
		$submissions = ArrayMethods::toObject($submissions);

		$klass = array();
		$klass["title"] = $grade->title;
		$klass["section"] = $classroom->section;
		$klass["year"] = $classroom->year;
		$klass = ArrayMethods::toObject($klass);

		$view->set("class", $klass);
		$view->set("submissions", $submissions);
		$view->set("assignment", $assignment);
	}

	/**
	 * @before _secure
	 */
	public function result($assignment_id) {
		$this->JSONView();
		$view = $this->getActionView();

		$result = Submission::first(array("assignment_id = ?" => $assignment_id, "user_id = ?" => $this->user->id));
		$view->set("result", $result);
	}

	/**
	 * @before _secure, _teacher
	 */
	public function gradeIt($submission_id) {
		$this->setSEO(array("title" => "Grade assignments | Teacher"));
		$view = $this->getActionView();

		$submission = Submission::first(array("id = ?" => $submission_id));
		$assignment = Assignment::first(array("id = ?" => $submission->assignment_id), array("id", "user_id"));
		
		if (!$submission || !$assignment || $assignment->user_id != $this->user->id) {
			self::redirect("/404");
		}

		$submission = Submission::first(array("assignment_id = ?" => $assignment->id, "user_id = ?" => $user_id));

		if (RequestMethods::post("action") == "saveMarks") {
			$submission->grade = RequestMethods::post("grade");
			$submission->remarks = RequestMethods::post("remarks");

			if ($submission->validate()) {
				$submission->save();
				$view->set("success", "Assignment successfully graded");
			} else {
				$view->set("errors", $submission->errors);
			}
		}
	}
}
