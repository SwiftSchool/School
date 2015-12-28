<?php

// initialize seo
include("seo.php");

$seo = new SEO(array(
    "title" => "CloudEducate - School Management ERP",
    "keywords" => "School management system, erp",
    "description" => "CloudEducate is an online School Management System that adapts to your school management system, automating the system",
    "author" => "CloudStuff.Tech",
    "robots" => "INDEX,FOLLOW",
    "photo" => CDN . "images/logo.png"
));

Framework\Registry::set("seo", $seo);
