<?php
namespace MaDnh\Request;

class Response
{
    public $request;
    public $headers;
    public $response;
    public $error = false;
    public $start = 0;
    public $end = 0;

    /**
     * Check request is error
     * @return bool
     */
    public function isError()
    {
        return is_array($this->error);
    }

    public function __toString()
    {
        return $this->response;
    }
}