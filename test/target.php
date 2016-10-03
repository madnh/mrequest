<?php
session_start();
if (!function_exists('getallheaders')) {
    function getallheaders()
    {
        $headers = '';
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}
var_dump(array(
    'SERVER' => $_SERVER,
    'HEADER' => getallheaders(),
    'GET' => $_GET,
    'POST' => $_POST,
    'COOKIE' => $_COOKIE
));