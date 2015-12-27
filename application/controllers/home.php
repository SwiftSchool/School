<?php

/**
 * The Home Controller
 *
 * @author Faizan Ayubi, Hemant Mann
 */
use Shared\Controller as Controller;

class Home extends Controller {

    public function index() {
        $this->seo(array(
            "title" => "Cloudeducate",
            "keywords" => "school",
            "description" => "School Website",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
    }

    public function pricing() {
    	$this->seo(array(
            "title" => "Cloudeducate | Pricing",
            "keywords" => "school",
            "description" => "School Website",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
    }

    public function contact() {
    	$this->seo(array(
            "title" => "Cloudeducate | Contact Us",
            "keywords" => "school",
            "description" => "School Website",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
    }
}
