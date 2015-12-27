<?php

/**
 * The Teachers Controller
 *
 * @author Hemant Mann
 */
use Framework\Registry as Registry;
use Framework\RequestMethods as RequestMethods;

class Teacher extends School {
    
    /**
     * @readwrite
     */
    protected $_educator;

    /**
     * @readwrite
     */
    protected $_organization;

    public function render() {
        if ($this->educator) {
            if ($this->actionView) {
                $this->actionView->set("__educator", $this->educator);
            }

            if ($this->layoutView) {
                $this->layoutView->set("__educator", $this->educator);
            }
        }

        if ($this->organization) {
            if ($this->actionView) {
                $this->actionView->set("__organization", $this->organization);
            }

            if ($this->layoutView) {
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
		$this->setSEO(array("title" => "Profile"));
        $view = $this->getActionView();
	}

    /**
     * @before _secure, _school
     */
    public function add() {
        $this->setSEO(array("title" => "Add Teachers"));
        $view = $this->getActionView();

        if (RequestMethods::post("action") == "addTeachers") {
            $teachers = $this->reArray($_POST);
            foreach ($teachers as $teacher) {
                $user = $this->_createUser($teacher);
                if (isset($user)) {
                    try {
                        $educator = new Educator(array(
                            "organization_id" => $this->organization->id,
                            "user_id" => $user->id,
                            "location_id" => $location->id
                        ));
                        $educator->save();
                    } catch (\Exception $e) {
                        continue;
                    }
                    
                }
            }

            $view->set("success", 'Teachers saved successfully!! See <a href="/teacher/manage">Manage Teachers');
        }
    }

    /**
     * @before _secure, _school
     */
    public function manage() {
        $this->setSEO(array("title" => "Manage Teachers"));
        $view = $this->getActionView();

        $teachers = Educator::all(array("organization_id = ?" => $this->organization->id), array("*"), "created", "desc", 30, 1);
        $view->set("teachers", $teachers);
    }

    /**
     * @before _secure, _school
     */
    public function allot() {
        $this->setSEO(array("title" => "Allot Teachers to different classes"));
        $view = $this->getActionView();

        $teachers = \Educator::all(array("organization_id = ?" => $this->organization->id));
        $view->set("teachers", $teachers);

        // @todo - how to store which teacher which subject to which class
    }

    /**
     * @protected
     */
    public function _teacher() {
        $this->organization = Registry::get("session")->get("organization");
        $this->educator = Registry::get("session")->get("educator");
        if (!$this->organization && !$this->educator) {
            self::redirect("/");
        }

        $this->defaultLayout = "layouts/teacher";
        $this->setLayout();
    }

}
