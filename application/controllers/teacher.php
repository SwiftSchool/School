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
    public function edit($teacher_id) {
        $teacher = \Educator::first(array("id = ?" => $teacher_id), array("user_id", "organization_id"));
        if (!$teacher || $teacher->organization_id != $this->organization->id) {
            self::redirect("/school");
        }

        $this->setSEO(array("title" => "Profile"));
        $view = $this->getActionView();

        $usr = \User::first(array("id = ?" => $teacher->user_id));
        if (RequestMethods::post("action") == "editTeacher") {
            $email = RequestMethods::post("email");
            $phone = RequestMethods::post("phone");

            $emailExist = ($email != $usr->email) ? \User::first(array("email = ?" => $email), array("id")) : false;
            $phoneExist = ($phone != $usr->phone) ? \User::first(array("phone = ?" => $phone), array("id")) : false;

            if ($emailExist) {
                $view->set("error", true);
                $view->set("message", "Failed to edit the teacher! Email already exists");
            } elseif ($phoneExist) {
                $view->set("error", true);
                $view->set("message", "Phone number already exists!! Enter different phone");
            } else {
                $usr->name = RequestMethods::post("name");
                $usr->email = $email;
                $usr->phone = $phone;

                $usr->save();
                $view->set("message", "Teacher edited successfully!!");    
            }
        }

        $view->set("teacher", $usr);
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
