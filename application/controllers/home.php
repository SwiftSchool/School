<?php

/**
 * The Home Controller
 *
 * @author Faizan Ayubi, Hemant Mann
 */
use Shared\Controller as Controller;
use Framework\Registry as Registry;

class Home extends Controller {

    public function index() {
        $this->getLayoutView()->set("seo", Framework\Registry::get("seo"));
    }

    public function pricing() {
    	$this->seo(array(
            "title" => "Pricing",
            "keywords" => "school",
            "description" => "School Website",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
    }

    public function contact() {
    	$this->seo(array(
            "title" => "Contact Us",
            "keywords" => "school",
            "description" => "School Website",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
    }
}
