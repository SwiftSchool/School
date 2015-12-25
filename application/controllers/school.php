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
	 * Stores the dashboard redirect url
	 */
	protected $_dashboard = "/school";

	/**
     * @protected
     */
    public function _admin() {
    	parent::_admin();
        if ($this->user->type != 'educator') {
            self::redirect("/404");
        }

        $this->changeLayout();
    }

    /**
     * @before _secure, _admin
     */
	public function index() {
		$this->setSEO(array("title" => "Admin | School | Dashboard"));
		$view = $this->getActionView();

		$counts = array();
		$counts["students"] = Student::count(array("organization_id = ?" => $this->school->id));
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
	 * @before _secure, _admin
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
