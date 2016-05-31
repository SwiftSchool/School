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
        "pattern" => "student/dashboard",
        "controller" => "student",
        "action" => "index"
    ),
    array(
        "pattern" => "teacher/dashboard",
        "controller" => "teacher",
        "action" => "index"
    ),
    array(
        "pattern" => "login",
        "controller" => "auth",
        "action" => "login"
    ),
    array(
        "pattern" => "index",
        "controller" => "home",
        "action" => "index"
    ),
    array(
        "pattern" => "student/messages",
        "controller" => "conversation",
        "action" => "find"
    )
);

// add defined routes
foreach ($routes as $route) {
    $router->addRoute(new Framework\Router\Route\Simple($route));
}

// unset globals
unset($routes);
