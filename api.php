<?php
/**
 * This is an example class script proceeding secured API
 * To use this class you should keep same as query string and function name
 *
 * usage:
 * $object->response(output_data, status_code);
 * $object->_request	- to get santinized input
 *
 * output_data : JSON (I am using)
 * status_code : Send status message for headers
 *
 */
	
require_once("Rest.inc.php");

/**
 * Class API
 * @file: api.php
 * @author: Harisankar.M.R <mrsank@live.in>
 * @date: 08.07.2015
 *
 */
class API extends REST
{

    /**
     * @var string $data
     */
    public $data = "";

    /**
     * @constant string DB_SERVER
     */
    const DB_SERVER = "localhost";

    /**
     * @constant string DB_USER
     */
    const DB_USER = "root";

    /**
     * @constant string DB_PASSWORD
     */
    const DB_PASSWORD = "";

    /**
     * @constant string DB
     */
    const DB = "rest";

    /**
     * @var null $db
     */
    private $db = NULL;


    /**
     * API constructor.
     */
    public function __construct()
    {
        parent::__construct();				// Init parent contructor
        $this->dbConnect();					// Initiate Database connection
    } // function


    /**
     * Database connection function
     */
    private function dbConnect()
    {
        $this->db = mysql_connect(self::DB_SERVER,self::DB_USER,self::DB_PASSWORD);
        if($this->db)
        {
            mysql_select_db(self::DB,$this->db);
        } // if
    } // function


    /**
     * Public method for access api
     * This method dynamically call the method based on query string
     */
    public function processApi()
    {
        $func = strtolower(trim(str_replace("/","",$_REQUEST['rquest'])));
        if((int)method_exists($this,$func) > 0)
        {
            $this->$func();
        }
        else
        {
            $this->response('',404);				// If the method not exist with in this class, response would be "Page not found".
        } // if..else..
    } // function


    /**
     * Simple login API
     * Login must be POST method
     * email: <USER EMAIL>
     * pwd: <USER PASSWORD>
     */
    private function login()
    {
        // Cross validation if the request method is POST else it will return "Not Acceptable" status
        if($this->get_request_method() != "POST")
        {
            $this->response('',406);
        } // if

        $email = $this->_request['email'];
        $password = $this->_request['pwd'];

        // Input validations
        if(!empty($email) and !empty($password))
        {
            if(filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                $sql = mysql_query("SELECT user_id, user_fullname, user_email FROM users WHERE user_email = '$email' AND user_password = '".md5($password)."' LIMIT 1", $this->db);
                if(mysql_num_rows($sql) > 0)
                {
                    $result = mysql_fetch_array($sql,MYSQL_ASSOC);

                    // If success everythig is good send header as "OK" and user details
                    $this->response($this->json($result), 200);
                } //if
                $this->response('', 204);	// If no records "No Content" status
            } // if
        }

        // If invalid inputs "Bad Request" status message and reason
        $error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
        $this->response($this->json($error), 400);

    } // function


    /**
     * Create user function
     */
    private function createUser()
    {
        if($this->get_request_method() != "PUT")
        {
            $this->response('',406);
        } // if

        $name = $this->_request['name'];
        $email = $this->_request['email'];
        $password = $this->_request['pwd'];
        $status = 1;

        // Checking if the user with same email already exist
        $sql = mysql_query("SELECT user_id, user_fullname, user_email FROM users WHERE user_email = '$email'", $this->db);
        if(mysql_num_rows($sql) > 0)
        {
            $result = array('status' => "Failed", "msg" => "User already exist");
            $this->response($this->json($result), 200);
        } // if

        // Creating user if user with already existing email not found inside
        if(!empty($name) and !empty($email) and !empty($password))
        {
            if(filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                $query = mysql_query("INSERT INTO `users`(`user_fullname`, `user_email`, `user_password`, `user_status`) VALUES ('$name','$email',md5('$password'),$status)", $this->db);
                $success = array('status' => "Success", "msg" => "Successfully one record added.");
                $this->response($this->json($success),200);
            } // if
            $this->response($this->json($result), 200);
        } // if

    } // function


    /**
     * User function
     */
    private function users()
    {
        // Cross validation if the request method is GET else it will return "Not Acceptable" status
        if($this->get_request_method() != "GET")
        {
            $this->response('',406);
        } // if
        $sql = mysql_query("SELECT user_id, user_fullname, user_email FROM users WHERE user_status = 1", $this->db);
        if(mysql_num_rows($sql) > 0)
        {
            $result = array();
            while($rlt = mysql_fetch_array($sql,MYSQL_ASSOC))
            {
                $result[] = $rlt;
            } // while
            // If success everythig is good send header as "OK" and return list of users in JSON format
            $this->response($this->json($result), 200);
        } // if
        $this->response('',204);	// If no records "No Content" status
    } // function


    /**
     * Delete user function
     */
    private function deleteUser()
    {
        // Cross validation if the request method is DELETE else it will return "Not Acceptable" status
        if($this->get_request_method() != "POST")
        {
            // Method changes to POST from DELETE as DELETE was not working in my pc
            $this->response('',406);
        } // if
        $id = (int)$this->_request['id'];


        if($id > 0)
        {
            // Checking if the user is existing with specefied user id
            $sql = mysql_query("SELECT user_id, user_fullname, user_email FROM users WHERE user_id = $id", $this->db);
            if(mysql_num_rows($sql) == 0)
            {
                $result = array('status' => "Failed", "msg" => "User not found");
                $this->response($this->json($result), 200);
            } // if

            // Deleting the user if existing with specefied id
            mysql_query("DELETE FROM users WHERE user_id = $id");
            $success = array('status' => "Success", "msg" => "Successfully one record deleted.");
            $this->response($this->json($success),200);
        }
        else
        {
            $this->response('',204);	// If no records "No Content" status
        } // if...else...
    } // function


    /**
     * @param $data
     * @return string
     *
     * Encode array into JSON
     */
    private function json($data)
    {
        if(is_array($data))
        {
            return json_encode($data);
        } // if
    } // function

} // class

// Initiiate Library

$api = new API;
$api->processApi();