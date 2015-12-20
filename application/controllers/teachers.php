<?php

/**
 * The Teachers Controller
 *
 * @author Hemant Mann
 */
use Framework\Registry as Registry;
use Framework\RequestMethods as RequestMethods;

class Teachers extends Users {
    /**
     * @protected
     */
    public function _admin() {
        parent::_admin();

        $this->dashboard = "/school_admin";
        $this->defaultLayout = "layouts/school_admin";
        $this->setLayout();
    }
    
    /**
     * @readwrite
     * Stores the dashboard redirect url
     */
    protected $_dashboard = "/teachers/dashboard";

    /**
     * @readwrite
     */
    protected $_teacher;

    /**
     * @readwrite
     */
    protected $_school;

    protected function setTeacher($teacher) {
        $session = Registry::get("session");
        if ($teacher) {
            $session->set("teacher", $teacher);
        } else {
            $session->erase("teacher");
        }
        $this->_teacher = $teacher;
        return $this;
    }

    public function __construct($options = array()) {
        parent::__construct($options);

        $session = Registry::get("session");
        $this->teacher = $session->get("teacher");
        $this->school = $session->get("school");
    }

    public function render() {
        if ($this->teacher) {
            if ($this->actionView) {
                $this->actionView->set("__teacher", $this->teacher);
                $this->actionView->set("__school", $this->school);
            }

            if ($this->layoutView) {
                $this->layoutView->set("__teacher", $this->teacher);
                $this->layoutView->set("__school", $this->school);
            }
        }
        parent::render();
    }

    /**
     * @protected
     */
    public function _teacher() {
        if (!$this->teacher) {
            self::redirect("/");
        }
        $this->changeLayout();
    }

    protected function _verifyInput($model, $fields) {
        $check = $model::first($fields);
        if (!$check) {
            self::redirect($this->dashboard);
        } else {
            return $check;
        }
    }

	/**
	 * @before _secure, _teacher
	 */
	public function index() {
		$this->setSEO(array("title" => "Teachers | Dashboard"));
        $view = $this->getActionView();
	}

	/**
	 * @before _secure, _teacher
	 */
	public function profile() {
		$this->setSEO(array("title" => "Teachers | Profile"));
        $view = $this->getActionView();
	}

    /**
     * @before _secure, _admin
     */
    public function add() {
        $this->setSEO(array("title" => "School | Add Teachers"));
        $view = $this->getActionView();

        if (RequestMethods::post("action") == "addTeachers") {
            $message = $this->_saveUser(array("type" => "teacher"));
            if (isset($message["error"])) {
                $view->set("success", $message["error"]);
            } else {
                $view->set("success", 'Teachers saved successfully!! See <a href="/teachers/manage">Manage Teachers');
            }
        }
    }

    /**
     * @before _secure, _admin
     */
    public function manage() {
        $this->setSEO(array("title" => "School | Manage Teachers"));
        $view = $this->getActionView();

        $teachers = Teacher::all(array("school_id = ?" => $this->school->id), array("*"), "created", "desc", 30, 1);
        $view->set("teachers", $teachers);
    }

}
