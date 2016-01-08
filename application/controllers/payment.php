<?php
/**
 * The Payment Controller
 *
 * @author Hemant Mann
 */
use Framework\RequestMethods as RequestMethods;
use Shared\Markup as Markup;
use Framework\Registry as Registry;
use Framework\ArrayMethods as ArrayMethods;

class Payment extends School {
	
	/**
	 * @before _secure, _school
	 */
	public function createfee() {
		$this->setSEO(array("title" => "Fee | School"));
		$view = $this->getActionView();

		$grades = \Grade::all(array("organization_id = ?" => $this->organization->id), array("id", "title"));
		$view->set("grades", $grades);

		if (RequestMethods::post("action") == "createFee") {
			
		}
	}

	/**
	 * @before _secure, _school
	 */
	public function records() {
		$this->setSEO(array("title" => "Manage Payment | School"));
		$view = $this->getActionView();

		$grades = Grade::all(array("organization_id = ?" => $this->organization->id), array("id", "title"));
		$view->set("grades", $grades);
		$view->set("courses", array());
		$view->set("enrollments", array());
	}

	/**
	 * @before _secure, _school
	 */
	public function add() {
		$this->setSEO(array("title" => "Add Payment | School"));
		$view = $this->getActionView();

		$grades = \Grade::all(array("organization_id = ?" => $this->organization->id), array("id", "title"));
		$view->set("grades", $grades);
	}
}