<?php

// define routes

$routes = array(
    array(
        "pattern" => "features",
        "controller" => "home",
        "action" => "features"
    ),
    array(
        "pattern" => "home",
        "controller" => "home",
        "action" => "index"
    ),
    array(
        "pattern" => "students/dashboard",
        "controller" => "students",
        "action" => "index"
    ),
    array(
        "pattern" => "teachers/dashboard",
        "controller" => "teachers",
        "action" => "index"
    ),
    array(
        "pattern" => "school_admin/login",
        "controller" => "teachers",
        "action" => "login"
    )
);

// add defined routes
foreach ($routes as $route) {
    $router->addRoute(new Framework\Router\Route\Simple($route));
}

// unset globals
unset($routes);
