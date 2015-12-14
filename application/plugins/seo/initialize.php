<?php

// initialize seo
include("seo.php");

$seo = new SEO(array(
    "title" => "SwiftDetectr",
    "keywords" => "Triggers action for users",
    "description" => "Welcome to Our Trigger Network",
    "author" => "CloudStuff.Tech",
    "robots" => "INDEX,FOLLOW",
    "photo" => CDN . "img/logo.png"
));

Framework\Registry::set("seo", $seo);
