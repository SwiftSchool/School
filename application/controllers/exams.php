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
	 * @protected
	 */
	public function changeLayout() {
		$this->defaultLayout = "layouts/school";
		$this->setLayout();
	}

	/**
	 * @before _secure, _admin
	 */
	public function create() {
		$this->setSEO(array("title" => "School | Add Courses"));
		$view = $this->getActionView();

		if (RequestMethods::post("action") == "createExam") {
			// @todo - process creation of examination
		}
	}
}