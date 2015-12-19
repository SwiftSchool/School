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

class Classrooms extends School_Admin {
	/**
     * @before _secure, _admin
     */
	public function add($grade_id) {
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
					"grade_id" => $grade_id
				));
				// Incomplete - @todo future-commits
			}
		}
	}

	/**
     * @before _secure, _admin
     */
	public function manage() {

	}
}
