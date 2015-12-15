<?php
/**
 * The School Admin Controller
 *
 * @author Hemant Mann
 */
use Framework\RequestMethods as RequestMethods;
use Shared\Markup as Markup;
use Framework\Registry as Registry;

class SchoolAdmin extends Teacher {
	/**
     * @protected
     */
    public function _admin() {
    	parent::_admin();
        if ($this->user->type != 'teacher') {
            self::redirect("/404");
        }
    }

	/**
	 * @before _secure, _admin
	 */
	public function index() {
		$this->seo(array(
			"title" => "e-Learning",
			"keywords" => "dashboard",
			"description" => "Students/Parents Dashboard",
			"view" => $this->getLayoutView()
		));
		$view = $this->getActionView();
	}

	/**
	 * @before _secure, _admin
	 */
	public function addStudent() {

	}

	/**
	 * @before _secure, _admin
	 */
	public function addTeacher() {

	}

	/**
	 * @before _secure, _admin
	 */
	public function manageStudents() {

	}

	/**
	 * @before _secure, _admin
	 */
	public function manageTeachers() {

	}
}