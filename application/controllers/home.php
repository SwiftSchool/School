<?php

/**
 * The Home Controller
 *
 * @author Faizan Ayubi, Hemant Mann
 */
use Shared\Controller as Controller;
use Framework\Registry as Registry;

class Home extends Controller {
    /**
     * Will set variables to all the views of a controller
     */
    public function render() {
        $session = Registry::get("session");
        if ($this->actionView) {
            $this->actionView->set("educator", $session->get("educator"))
                            ->set("scholar", $session->get("scholar"))
                            ->set("organization", $session->get("organization"));
        }

        if ($this->layoutView) {
            $this->layoutView->set("educator", $session->get("educator"))
                            ->set("scholar", $session->get("scholar"))
                            ->set("organization", $session->get("organization"));
        }
        parent::render();
    }

    public function index() {
        $this->setSEO(array("title" => "School | ERP"));
        $view = $this->getActionView();
    }

    public function pricing() {
    	$this->setSEO(array("title" => "Pricing"));
        $view = $this->getActionView();
    }

    public function contact() {
    	$this->setSEO(array("title" => "Contact Us"));
        $view = $this->getActionView();
    }

    public function test() {
        $this->JSONView();
        $view = $this->getActionView();

        foreach ($_POST as $key => $value) {
            $view->set($key, $value);
        }
    }
}
