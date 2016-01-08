<?php
/**
 * The Exams Controller
 *
 * @author Hemant Mann
 */
use Framework\RequestMethods as RequestMethods;
use Shared\Markup as Markup;
use Framework\Registry as Registry;
use Framework\ArrayMethods as ArrayMethods;

class Exams extends School {
	/**
	 * @before _secure, _school
	 */
	public function create() {
		$this->setSEO(array("title" => "Create Exam | School"));
		$view = $this->getActionView();

		$grades = Grade::all(array("organization_id = ?" => $this->organization->id), array("id", "title"));
		if (RequestMethods::post("action") == "createExam") {
			$exams = $this->reArray($_POST);

			foreach ($exams as $e) {
				$course = $e["course"];

				if (Markup::checkValue($course)) {
					$exam = new Exam(array(
						"grade_id" => $e["grade"],
						"course_id" => $course,
						"user_id" => $this->user->id,
						"organization_id" => $this->organization->id,
						"type" => $e["type"],
						"start_date" => $e["start_date"],
						"start_time" => $e["start_time"] .":00",
						"end_time" => $e["end_time"] .":00"
					));
					$exam->save();
				}
			}
			$view->set("success", "Exams created successfully!!");
		}
		$view->set("grades", $grades);
	}

	/**
	 * @before _secure, _school
	 */
	public function manage() {
		$this->setSEO(array("title" => "Create Exam | School"));
		$view = $this->getActionView();

		$limit = RequestMethods::get("limit", 10);
		$page = RequestMethods::get("page", 1);

		$exams = Exam::all(array("organization_id = ?" => $this->organization->id), array("*"), "created", "desc", $limit, $page);
		$count = Exam::count(array("organization_id = ?" => $this->organization->id));
		$grades = Grade::all(array("organization_id = ?" => $this->organization->id), array("id", "title"));
		$setGrades = array();
        foreach ($grades as $g) {
            $setGrades["$g->id"] = $g->title;
        }

		$view->set("limit", $limit);
		$view->set("page", $page);
		$view->set("count", $count);
		$view->set("exams", $exams);
		$view->set("grades", $setGrades);
	}

	/**
	 * @before _secure, _school
	 */
	public function edit($exam_id) {
		$exam = Exam::first(array("id = ?" => $exam_id));
		if (!$exam || $exam->organization_id != $this->organization->id) {
			self::redirect("/school");
		}

		$this->setSEO(array("title" => "Edit Exam | School"));
		$view = $this->getActionView();

		if (RequestMethods::post("action") == "editExam") {
			// process editing
		}

	}

	/**
	 * @before _secure, _school
	 */
	public function result() {
		$this->setSEO(array("title" => "Exam Results | School"));
		$view = $this->getActionView();
		$session = Registry::get("session");

		$grades = Grade::all(array("organization_id = ?" => $this->organization->id), array("id", "title"));
		$view->set("grades", $grades);
		$view->set("courses", array());
		$view->set("enrollments", array());

		if (RequestMethods::post("action") == "findStudents") {
			$classroom_id = RequestMethods::post("classroom_id");
			$grade_id = RequestMethods::post("grade");
			$exam_title = RequestMethods::post("exam");
			$enrollments = Enrollment::all(array("classroom_id = ?" => $classroom_id));
			$exams = Exam::all(array("grade_id = ?" => $grade_id, "type = ?" => $exam_title), array("id", "grade_id", "course_id"));

			$courses = array();
			foreach ($exams as $e) {
				$c = Course::first(array("id = ?" => $e->course_id), array("title"));
				$courses[] = array(
					"title" => $c->title,
					"id" => $e->course_id
				);
			}
			$courses = ArrayMethods::toObject($courses);
			$session->set('Exams\Result:$exams', $exams);
			$session->set('Exams\Result:$grade_id', $grade_id);

			$view->set("courses", $courses);
			$view->set("exams", $exams);
			$view->set("enrollments", $enrollments);
		}

		if (RequestMethods::post("action") == "saveMarks") {
			$exams = $session->get('Exams\Result:$exams');
			$grade_id = $session->get('Exams\Result:$grade_id');
			
			$ids = RequestMethods::post("user_id");
			$user_id = RequestMethods::post("user_id");
			$marks = '';
			
			$total = count($exams);
			foreach ($user_id as $key => $value) {
				for ($i = 0; $i < $total; ++$i) {
					$marks = RequestMethods::post($exams[$i]->id."_marks");
					$result = new ExamResult(array(
						"exam_id" => $exams[$i]->id,
						"grade_id" => $grade_id,
						"user_id" => $user_id[$key],
						"marks" => $marks[$key]
					));

					if ($result->validate()) {
						$result->save();
					}
				}
			}

			$view->set("success", "Result saved");
		}
	}

	/**
	 * @before _secure, _school
	 */
	public function marks() {
		$this->setSEO(array("title" => "View Marks | School"));
		$view = $this->getActionView();
		$session = Registry::get("session");

		$view->set("results", array());
		$grades = Grade::all(array("organization_id = ?" => $this->organization->id));
		
		if (RequestMethods::post("action") == "findStudents") {
			$grade = RequestMethods::post("grade");
			$exam_type = RequestMethods::post("exam");
			$classroom_id = RequestMethods::post("classroom_id");

			/*** Stores courses in an array ***/
			$courses = Course::all(array("grade_id = ?" => $grade), array("id", "title"));
			$setCourses = array();
			foreach ($courses as $c) {
				$setCourses["$c->id"] = $c->title;
			}

			/*** Store exams in an array ***/
			$exams = Exam::all(array("type = ?" => $exam_type, "grade_id = ?" => $grade));
			foreach ($exams as $e) {
				$setExams["$e->id"] = array("course_id" => $e->course_id);
			}

			/*** Find all students in class and store his details in an array ***/
			$users = Enrollment::all(array("classroom_id = ?" => $classroom_id));
			$results = array();
			foreach ($users as $u) {
				$usr = User::first(array("id = ?" => $u->user_id), array("name"));
				$scholar = Scholar::first(array("user_id = ?" => $u->user_id), array("roll_no"));
				$result = ExamResult::all(array("user_id = ?" => $u->user_id));
				
				/*** We need to find marks of the all the subject ***/
				$marks = array();
				foreach ($result as $r) {
					if (!array_key_exists($r->exam_id, $setExams)) {
						continue;
					}
					$c_id = $setExams["$r->exam_id"]["course_id"];
					$marks[] = array(
						"subject" => $setCourses["$c_id"],
						"marks" => $r->marks
					);
				}
				$results[] = array(
					"name" => $usr->name,
					"roll_no" => $scholar->roll_no,
					"results" => $marks
				);
			}
			
			$session->set('Exams\Marks:$marks', ArrayMethods::toObject($marks));
			$session->set('Exams\Marks:$results', ArrayMethods::toObject($results));
		}

		$view->set("marks", $session->get('Exams\Marks:$marks'));
		$view->set("results", $session->get('Exams\Marks:$results'));
		$view->set("grades", $grades);
	}
}