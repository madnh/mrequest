<?php
require 'MaDnh\mRequest.php';
use MaDnh\mRequest;

define('CURDIR', dirname(__FILE__));
mRequest::globalConfig('cookie_send', CURDIR . '/cookie.txt');
mRequest::globalConfig('cookie_save', CURDIR . '/cookie.txt');

$m = new mRequest(array(
    'timeout' => 10,
    'cookie_send' => CURDIR . '/cookie2.txt',
//    'cookie_save' => CURDIR.'/cookie2.txt',
));
$result = $m->get('http://www.w3schools.com/');
print_r($result);