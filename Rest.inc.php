<?php

/**
 * Class REST
 * File: Rest.inc.php
 * @author: Harisankar.M.R <mrsank@live.in>
 * @date: 08.07.2015
 */

class REST
{
    /**
     * @var array $_allow
     */
    public $_allow = array();

    /**
     * @var string $_content_type
     */
    public $_content_type = "application/json";

    /**
     * @var array $_request
     */
    public $_request = array();

    /**
     * @var string $_method
     */
    private $_method = "";

    /**
     * @var int $_code
     */
    private $_code = 200;

    /**
     * REST constructor.
     */
    public function __construct()
    {
        $this->inputs();
    } // function


    /**
     * @return mixed
     *
     * HTTP_REFERER setting
     */
    public function get_referer()
    {
        return $_SERVER['HTTP_REFERER'];
    } // function


    /**
     * @param $data
     * @param $status
     *
     * Response function
     */
    public function response($data,$status)
    {
        $this->_code = ($status)?$status:200;
        $this->set_headers();
        echo $data;
        exit;
    } // function


    /**
     * @return mixed
     *
     * Status message based on the request
     */
    private function get_status_message()
    {
        $status = array(
                    100 => 'Continue',
                    101 => 'Switching Protocols',
                    200 => 'OK',
                    201 => 'Created',
                    202 => 'Accepted',
                    203 => 'Non-Authoritative Information',
                    204 => 'No Content',
                    205 => 'Reset Content',
                    206 => 'Partial Content',
                    300 => 'Multiple Choices',
                    301 => 'Moved Permanently',
                    302 => 'Found',
                    303 => 'See Other',
                    304 => 'Not Modified',
                    305 => 'Use Proxy',
                    306 => '(Unused)',
                    307 => 'Temporary Redirect',
                    400 => 'Bad Request',
                    401 => 'Unauthorized',
                    402 => 'Payment Required',
                    403 => 'Forbidden',
                    404 => 'Not Found',
                    405 => 'Method Not Allowed',
                    406 => 'Not Acceptable',
                    407 => 'Proxy Authentication Required',
                    408 => 'Request Timeout',
                    409 => 'Conflict',
                    410 => 'Gone',
                    411 => 'Length Required',
                    412 => 'Precondition Failed',
                    413 => 'Request Entity Too Large',
                    414 => 'Request-URI Too Long',
                    415 => 'Unsupported Media Type',
                    416 => 'Requested Range Not Satisfiable',
                    417 => 'Expectation Failed',
                    500 => 'Internal Server Error',
                    501 => 'Not Implemented',
                    502 => 'Bad Gateway',
                    503 => 'Service Unavailable',
                    504 => 'Gateway Timeout',
                    505 => 'HTTP Version Not Supported'
        );  // array
        return ($status[$this->_code])?$status[$this->_code]:$status[500];
    } // function


    /**
     * @return mixed
     *
     * Return REQUEST_METHOD
     */
    public function get_request_method()
    {
        return $_SERVER['REQUEST_METHOD'];
    } // function


    /**
     * Case selection based on the method
     */
    private function inputs()
    {
        switch($this->get_request_method())
        {
            case "POST":
                $this->_request = $this->cleanInputs($_POST);
                break;
            case "GET":
            case "DELETE":
                $this->_request = $this->cleanInputs($_GET);
                break;
            case "PUT":
                parse_str(file_get_contents("php://input"),$this->_request);
                $this->_request = $this->cleanInputs($this->_request);
                break;
            default:
                $this->response('',406);
                break;
        } // switch
    } // function


    /**
     * @param $data
     * @return array|string
     *
     * Cleaning the input data
     */
    private function cleanInputs($data)
    {
        $clean_input = array();
        if(is_array($data))
        {
            foreach($data as $k => $v)
            {
                $clean_input[$k] = $this->cleanInputs($v);
            } // foreach
        }
        else
        {
            if(get_magic_quotes_gpc())
            {
                $data = trim(stripslashes($data));
            } // if
            $data = strip_tags($data);
            $clean_input = trim($data);
        } // if..else...
        return $clean_input;
    } // function


    /**
     * Setting the page header
     */
    private function set_headers()
    {
        header("HTTP/1.1 ".$this->_code." ".$this->get_status_message());
        header("Content-Type:".$this->_content_type);
    } // function

} // class