<?php

/**
 * The School Controller
 *
 * @author Hemant Mann
 */
use Framework\RequestMethods as RequestMethods;
use Shared\Markup as Markup;
use Framework\Registry as Registry;
use Framework\ArrayMethods as ArrayMethods;

class School extends Auth {

	/**
     * @readwrite
     */
    protected $_organization;

    /**
     * @protected
     */
    public function render() {
        if ($this->organization) {
            if ($this->actionView) {
                $this->actionView->set("organization", $this->organization);
            }

            if ($this->layoutView) {
                $this->layoutView->set("organization", $this->organization);
            }
        }
        parent::render();
    }

    public function logout() {
    	Registry::get("session")->erase("organization");
    	parent::logout();
    }

    /**
     * Registers new principal and school for platform access
     * @return [type] [description]
     */
    public function register() {
    	$this->setSEO(array("title" => "Register School"));
		$view = $this->getActionView();

		if (RequestMethods::post("action") == "register") {
			$user = new \User(array(
                "name" => RequestMethods::post("name"),
                "email" => RequestMethods::post("email"),
                "phone" => RequestMethods::post("phone"),
                "username" => strtolower(implode("", explode(" ", RequestMethods::post("name")))),
                "password" => Markup::encrypt("password"),
                "admin" => 0
            ));
            $user->save();

            $location = new Location(array(
            	"user_id" => $user->id,
            	"address" => RequestMethods::post("address"),
            	"city" => RequestMethods::post("city"),
            	"latitude" => "",
            	"longitude" => ""
            ));
            $location->save();

            $organization = new Organization(array(
            	"user_id" => $user->id,
            	"name" => RequestMethods::post("sname"),
            	"location_id" => $location->id,
            	"phone" => RequestMethods::post("sphone"),
            	"logo" => ""
            ));
            $organization->save();

            $view->set("success", true);
		}
    }

    /**
     * @before _secure, _school
     */
	public function index() {
		$this->setSEO(array("title" => "Admin | School | Dashboard"));
		$view = $this->getActionView();

		$counts = array();
		$counts["students"] = Scholar::count(array("organization_id = ?" => $this->organization->id));
		$counts["teachers"] = Educator::count(array("organization_id = ?" => $this->organization->id));
		$counts["classes"] = Grade::count(array("organization_id = ?" => $this->organization->id));
		$counts = ArrayMethods::toObject($counts);

		$session = Registry::get("session");
		$message = $session->get("redirectMessage");
		if ($message) {
			$view->set("message", $message);
			$session->erase("redirectMessage");
		}
		$view->set("counts", $counts);
	}

	/**
	 * @before _secure, _school
	 */
	public function misc() {
		$this->JSONView();
		$view = $this->getActionView();
		if (RequestMethods::post("action") == "process") {
			$opts = RequestMethods::post("opts");
			$query = $opts["query"];

			$where = array();
			foreach ($query as $q) {
				$where[$q["where"]] = $q["value"];
			}
			$fields = (isset($opts['fields']) ? $opts['fields'] : array("*")); 
			
			$check = $opts["model"]::all($where, $fields);
			if ($check) {
				$view->set("results", $check);
			} else {
				$view->set("error", true);
			}
		}
		
	}

	/**
	 * Manages all school registered
	 * @before _secure, _school, _admin
	 */
	public function all() {
		$this->setSEO(array("title" => "All Schools"));
		$view = $this->getActionView();

		$limit = RequestMethods::get("limit", 10);
		$page = RequestMethods::get("page", 1);

		$organizations = Organization::all(array(), array("name", "user_id", "created"), "created", "desc", $limit, $page);
		$view->set("organizations", $organizations);
	}

	/**
	 * @protected
	 */
	public function _school() {
		$this->organization = Registry::get("session")->get("organization");
        if (!$this->organization || $this->organization->user_id != $this->user->id) {
            self::redirect("/");
        }

        $this->defaultLayout = "layouts/school";
        $this->setLayout();
	}
	
}
