<?php

session_start();

define('BASE_URL', 'http://localhost/EcoVoyage/public/');
// define('BASE_URL', 'http://192.168.1.3/EcoVoyage/public/');

require_once("autoload.php");

new App();