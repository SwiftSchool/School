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
}