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
<<<<<<< HEAD
        "pattern" => "student/dashboard",
=======
        "pattern" => "contact",
        "controller" => "home",
        "action" => "contact"
    ),
    array(
        "pattern" => "pricing",
        "controller" => "home",
        "action" => "pricing"
    ),
    array(
        "pattern" => "students/dashboard",
>>>>>>> origin/master
        "controller" => "students",
        "action" => "index"
    ),
    array(
        "pattern" => "teachers/dashboard",
        "controller" => "teachers",
        "action" => "index"
    ),
    array(
        "pattern" => "login",
        "controller" => "auth",
        "action" => "login"
    )
);

// add defined routes
foreach ($routes as $route) {
    $router->addRoute(new Framework\Router\Route\Simple($route));
}

// unset globals
unset($routes);
