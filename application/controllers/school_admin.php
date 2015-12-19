<?php
/**
 * The School Admin Controller
 *
 * @author Hemant Mann
 */
use Framework\RequestMethods as RequestMethods;
use Shared\Markup as Markup;
use Framework\Registry as Registry;
use Framework\ArrayMethods as ArrayMethods;

class School_Admin extends Teachers {
	/**
	 * @readwrite
	 * Stores the dashboard redirect url
	 */
	protected $_dashboard = "/school_admin";

	/**
     * @protected
     */
    public function _admin() {
    	parent::_admin();
        if ($this->user->type != 'teacher') {
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
		$counts["students"] = Student::count(array("school_id = ?" => $this->school->id));
		$counts["teachers"] = Teacher::count(array("school_id = ?" => $this->school->id));
		$counts["classes"] = Grade::count(array("school_id = ?" => $this->school->id));
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
				$view->set("result", $check);
			} else {
				$view->set("error", true);
			}
		}
		
	}
	
}
