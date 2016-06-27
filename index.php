<?php
require 'src/MaDnh/Request.php';
require 'src/MaDnh/Response.php';
use MaDnh\Request;

define('CURDIR', dirname(__FILE__));
Request::globalConfig('cookie_send', CURDIR . '/cookie.txt');
Request::globalConfig('cookie_save', CURDIR . '/cookie.txt');

$m = new Request(array(
    'timeout' => 10,
    'cookie_send' => CURDIR . '/cookie2.txt',
//    'cookie_save' => CURDIR.'/cookie2.txt',
));
$result = $m->get('http://www.w3schools.com/');
echo $result->response;