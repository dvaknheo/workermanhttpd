<?php
WorkermanHttpd\WorkermanHttpd::session_start();
var_dump($_SESSION);
echo "!";
//WorkermanHttpd\WorkermanHttpd::exit(111);
$_SESSION[DATE(DATE_ATOM)]=DATE(DATE_ATOM);
var_dump($_SESSION);

var_dump(DATE(DATE_ATOM));
WorkermanHttpd\WorkermanHttpd::session_destroy();

var_dump($_SERVER);