<?php
require '../src/MaDnh/Request.php';
require '../src/MaDnh/Response.php';

use MaDnh\Request;

$m = new Request();

$result = $m->get('http://www.w3schools.com/');
var_dump($result);