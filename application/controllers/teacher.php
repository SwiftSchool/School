<?php

/**
 * The Teachers Controller
 *
 * @author Hemant Mann
 */
use Framework\Registry as Registry;
use Framework\RequestMethods as RequestMethods;

class Teacher extends Auth {
    
    /**
     * @readwrite
     */
    protected $_educator;

    /**
     * @readwrite
     */
    protected $_organization;

    public function __construct($options = array()) {
        parent::__construct($options);

        $this->organization = Registry::get("session")->get("organization");
        $this->educator = Registry::get("session")->get("educator");
        if (!$this->organization && !$this->educator) {
            self::redirect("/");
        }

        $this->defaultLayout = "layouts/teacher";
        $this->setLayout();
    }

    public function render() {
        if ($this->educator) {
            if ($this->actionView) {
                $this->actionView->set("__educator", $this->educator);
                $this->actionView->set("__organization", $this->organization);
            }

            if ($this->layoutView) {
                $this->layoutView->set("__educator", $this->educator);
                $this->layoutView->set("__organization", $this->organization);
            }
        }
        parent::render();
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
                $view->set("success", 'Teachers saved successfully!! See <a href="/teacher/manage">Manage Teachers');
            }
        }
    }

    /**
     * @before _secure, _admin
     */
    public function manage() {
        $this->setSEO(array("title" => "School | Manage Teachers"));
        $view = $this->getActionView();

        $teachers = Educator::all(array("organization_id = ?" => $this->school->id), array("*"), "created", "desc", 30, 1);
        $view->set("teachers", $teachers);
    }

    /**
     * @before _secure, _admin
     */
    public function allot() {
        $this->setSEO(array("title" => "School | Allot Teachers to different classes"));
        $view = $this->getActionView();

        $teachers = \Educator::all(array("organization_id = ?" => $this->school->id));
        $view->set("teachers", $teachers);

        // @todo - how to store which teacher which subject to which class
    }

}
