<?php
require '../src/MaDnh/Request.php';
require '../src/MaDnh/Response.php';

use MaDnh\Request;

$response = Request::url($_SERVER['HTTP_HOST'] . '/test/target.php')
    ->addCookie('is_admin', 'false')
    ->ajaxRequest()
    ->get(array(
        'name' => 'Tom',
        'old' => 15
    ));

echo '<h2>Start - End</h2>';
echo '<pre>';
print_r($response->start . ' -> ' . $response->end);
echo '</pre>';

echo '<h2>Headers</h2>';
echo '<pre>';
print_r($response->headers);
echo '</pre>';

echo '<h2>Error</h2>';
echo '<pre>';
var_dump($response->error);
echo '</pre>';

echo '<h2>Response</h2>';
echo '<pre>';
print_r($response->response);
echo '</pre>';