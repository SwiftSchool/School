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
            "title" => "e-Learning",
            "keywords" => "school",
            "description" => "School Website",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
    }

}
