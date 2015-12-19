<?php
/**
 * The Grades Controller
 *
 * @author Hemant Mann
 */
use Framework\RequestMethods as RequestMethods;
use Shared\Markup as Markup;
use Framework\Registry as Registry;
use Framework\ArrayMethods as ArrayMethods;

class Grades extends School_Admin {
	/**
     * @before _secure, _admin
     */
	public function add() {
		$this->setSEO(array("title" => "School | Add Grades"));
		$view = $this->getActionView();

		if (RequestMethods::post("action") == "addGrades") {
			$name = RequestMethods::post("name");
			$description = RequestMethods::post("description");

			foreach ($name as $key => $value) {
				$grade = new Grade(array(
					"title" => Markup::sanitize($value),
					"description" => Markup::sanitize($description[$key]),
					"school_id" => $this->school->id
				));
				$grade->save();
			}

			$view->set("success", 'Classes added successfully! Now <a href="/manage/grades">Manage Classes</a>');
		}
	}

    /**
     * @before _secure, _admin
     */
	public function manage() {
		$this->setSEO(array("title" => "School | Manage Classes"));
		$view = $this->getActionView();

		$grades = Grade::all(array("school_id = ?" => $this->school->id));
		$view->set("grades", $grades);
	}
}