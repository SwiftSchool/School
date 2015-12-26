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

class School extends Teachers {

	/**
     * @readwrite
     */
    protected $_organization;

    public function __construct($options = array()) {
        parent::__construct($options);

        $this->organization = Registry::get("session")->get("organization");
        if (!$this->organization && $this->organization->user_id != $this->user->id) {
            self::redirect("/");
        }

        $this->defaultLayout = "layouts/school";
        $this->setLayout();
    }

    public function register() {
    	$this->setSEO(array("title" => "Register School"));
		$view = $this->getActionView();

		if (RequestMethods::post("action") == "register") {
			$user = new \User(array(
                "name" => RequestMethods::post("name"),
                "email" => RequestMethods::post("email"),
                "phone" => RequestMethods::post("phone"),
                "username" => implode(" ", RequestMethods::post("name")),
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
     * @before _secure
     */
	public function index() {
		$this->setSEO(array("title" => "Admin | School | Dashboard"));
		$view = $this->getActionView();

		$counts = array();
		$counts["students"] = Scholar::count(array("organization_id = ?" => $this->school->id));
		$counts["teachers"] = Educator::count(array("organization_id = ?" => $this->school->id));
		$counts["classes"] = Grade::count(array("organization_id = ?" => $this->school->id));
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
	 * @before _secure
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
			
			$check = $opts["model"]::all($where);
			if ($check) {
				$view->set("results", $check);
			} else {
				$view->set("error", true);
			}
		}
		
	}
	
}
