<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Oauth extends CI_Controller {

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     * 		http://example.com/index.php/welcome
     * 	- or -
     * 		http://example.com/index.php/welcome/index
     * 	- or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see https://codeigniter.com/user_guide/general/urls.html
     */
    function __construct() {
        parent::__construct();
        $response = $this->ValidationModel->validateHeader();
        if ($response['success'] === FALSE) {
            echo json_encode($response);
            exit;
        }
        $this->load->library('sendinblue/Mailin');
    }

    public function signup() {
        $response = array();
        $response['success'] = FALSE;
        $response['error_type'] = 0;
        $response['message'] = "Something Wrong";

        $email = trim($this->input->post('email')); /* Email */
        $password = trim($this->input->post('password')); /* MD5 encryption */
        $name = trim($this->input->post('name')); /* MD5 encryption */

        $processFlag = true;

        if (!validateParam($email)) {
            $response['error_type'] = 1;
            $response['message'] = "Email ID field cannot be blank.";
            $processFlag = FALSE;
        } else if (!filter_email_domain($email)) {
            $response['error_type'] = 1;
            $response['message'] = "Please enter a valid email id.";
            $processFlag = FALSE;
        } else if (!validateParam($password)) {
            $response['error_type'] = 1;
            $response['message'] = "Please enter a valid password.";
            $processFlag = FALSE;
        } else if (!validateParam($name)) {
            $response['error_type'] = 1;
            $response['message'] = "Please enter a name.";
            $processFlag = FALSE;
        }
        if ($processFlag) {
            $where = "email='$email'";
//Get User Data 
            $userData = $this->CommonModel->getRow("users", $where, "user_id,email,email_verified");

            if (!empty($userData)) {
//                if ($userData['email_verified'] == 1) {
                $response['success'] = FALSE;
                $response['error_type'] = 1;
                $response['message'] = "Email id already registered! Please use a different email id.";
//                }
            } else {
                $register = array(
                    'email' => $email,
                    'password' => $password,
                    'name' => $name,
                    'email_verified' => 1,
                    'created_date' => date('Y-m-d H:i:s'),
                );
                $userId = $this->CommonModel->add('users', $register);
                $response = $this->generateToken($userId);
            }
        }
        echo json_encode($response);
    }

    function forgotPassword() {
        $response = array();
        $response['success'] = FALSE;
        $response['error_type'] = 0;
        $response['message'] = "Something Wrong";

        $email = trim($this->input->post('email')); /* Email */
        $processFlag = true;
        if (!validateParam($email)) {
            $response['error_type'] = 1;
            $response['message'] = "Email id field cannot be blank.";
            $processFlag = FALSE;
        } else if (!filter_email_domain($email)) {
            $response['error_type'] = 1;
            $response['message'] = "Please enter a valid email id.";
            $processFlag = FALSE;
        }
        if ($processFlag) {
            $where = "email='$email' AND email_verified=1";
//Get User Data 
            $userData = $this->CommonModel->getRow("users", $where, "user_id");
            if (!empty($userData)) {
                $userId = $userData['user_id'];
                $code = generateRandom();

                $update_data["otp"] = $code;
                $where = "user_id=$userId";
//Update User Data
                $this->CommonModel->edit('users', $update_data, $where);


               $mailin = new Mailin('https://api.sendinblue.com/v2.0', 'IPMmHnOTxjahF63Q');
                $subject = "Reset Password OTP";
                $message = "Hi $email,"
                       . "Your OTP for resetting your password is $code. Don't share this OTP with anyone.";
               $mail_data = array(
                   "to" => [$email => ''],
                   "cc" => [],
                   "bcc" => [],
                   "from" => array("vivek.kamdar3@gmail.com", "Recipe"),
                   "replyto" => array("support@recipe.com", "Recipe Support Team"),
                   "subject" => $subject,
                   "text" => "",
                   "html" => $message,
                   "attachment" => array(),
                   "headers" => array("Content-Type" => "text/html; charset=iso-8859-1", "X-param1" => "value1", "X-param2" => "value2", "X-Mailin-custom" => "my custom value", "X-Mailin-IP" => "213.32.159.50", "X-Mailin-Tag" => "My tag")
               );
               $mailin->send_email($mail_data);

                $response["success"] = TRUE;
                $response["userId"] = _encode($userId);
                $response["message"] = "You will receive an OTP shortly on your entered email id.";
            } else {
                $response["error_type"] = 1;
                $response["message"] = "This email id is not registered.";
            }
        } else {
            $response["error_type"] = 1;
        }
        echo json_encode($response);
    }

    function signin() {
        $response = array();
        $response['success'] = FALSE;
        $response['error_type'] = 0;
        $response['message'] = "Something Wrong";

        $email = trim($this->input->post("email")); /* Mobile No */
        $password = trim($this->input->post("password")); /* Password */ //MD5 from device or client side

        $processFlag = true;
        if (!validateParam($email)) {
            $response['error_type'] = 1;
            $response['message'] = "Email ID field cannot be blank.";
            $processFlag = FALSE;
        } else if (!filter_email_domain($email)) {
            $response['error_type'] = 1;
            $response['message'] = "Please enter a valid email id.";
            $processFlag = FALSE;
        } else if (!validateParam($password)) {
            $response['error_type'] = 1;
            $response['message'] = "Please enter a valid password.";
            $processFlag = false;
        }

        if ($processFlag) {
            $encryptedPassword = $password; //md5 (post from device)
            $where = "email='$email'";
//Get User Data
            $userData = $this->CommonModel->getRow("users", $where, "user_id,email,email_verified,password");
            if (!empty($userData)) {
                if ($userData['password'] == $encryptedPassword) {//Password Match
                    if ($userData['email_verified'] == 1) {//Existing User
//Generate Token & Send User Data for Login
                        $response = $this->generateToken($userData['user_id']);
                    } else {
                        $response['error_type'] = 1;
                        $response['message'] = "Please verify your email.";
                    }
                } else {
                    $response['error_type'] = 1;
                    $response['message'] = "Invalid credentials!";
                }
            } else {
                $response['error_type'] = 1;
                $response['message'] = "This email id is not registered.";
            }
        } else {
            $response["error_type"] = 1;
        }
        echo json_encode($response);
    }

    function verifyOTP() {
        $response = array();
        $response['success'] = FALSE;
        $response['error_type'] = 0;
        $response['message'] = "Something Wrong";

        $userId = trim(_decode($this->input->post('userId'))); /* User ID */ //encoded from client side
        $otp = trim($this->input->post("otp")); /* Mobile No */
//        $email = trim($this->input->post('email')); /* Email */

        $processFlag = TRUE;
        if (!validateParam($otp)) {
            $response['error_type'] = 1;
            $response['message'] = "Please enter the OTP sent to your registered Mobile number.";
            $processFlag = FALSE;
        }

        if ($processFlag) {
            $where = "user_id=$userId";
//Get User Data
            $userData = $this->CommonModel->getRow("users", $where, "user_id,email,email_verified,otp");
            if (!empty($userData)) {
                $db_otp = $userData['otp'];
                if ($otp == $db_otp) {//OTP match
                    $response['success'] = TRUE;
                    $response['error_type'] = 0;
                    $response["userId"] = _encode($userId);
                    $response["message"] = 'SUCCESS';
                } else {
                    $response['error_type'] = 1;
                    $response['message'] = "The entered OTP is not valid.";
                }
            } else {
                $response['error_type'] = 1;
                $response['message'] = "The entered OTP is not valid.";
            }
        } else {
            $response["error_type"] = 1;
        }
        echo json_encode($response);
    }

    function updatePassword() {
        $response = array();
        $response['success'] = FALSE;
        $response['error_type'] = 0;
        $response['message'] = "Something Wrong";

        $userId = trim(_decode($this->input->post('userId'))); /* User ID */ //encoded from client side
        $password = trim($this->input->post("password")); /* Password */ //MD5 from device or client side

        $processFlag = TRUE;
        if (!validateParam($password)) {
            $response['error_type'] = 1;
            $response['message'] = "Please enter a valid password.";
            $processFlag = false;
        }

        $where = "user_id=$userId";
        $userData = $this->CommonModel->getRow("users", $where, "*");

        if ($processFlag) {
            if (!empty($userData)) {
                $update_data["password"] = $password;
                $where = "user_id=$userId";
//Update User Data
                $this->CommonModel->edit('users', $update_data, $where);
                $response = $this->generateToken($userId);
                $response['success'] = TRUE;
                $response['message'] = "Password Change Successfully";
            } else {
                $response['error_type'] = 1;
                $response['message'] = "Please enter a valid password.";
            }
        } else {
            $response["error_type"] = 1;
        }
        echo json_encode($response);
    }

    function signout() {
        $response = array();
        $response['success'] = FALSE;
        $response['error_type'] = 0;
        $response['message'] = "Something Wrong";

        $user_id = _decode($this->input->get_request_header('ID', TRUE));

        if ($user_id == 0) {
            $response['success'] = true;
            $response['message'] = "You have Successfully Logged Out!";
            echo json_encode($response);
            exit;
        }
//Destroy API Session
        $validate = $this->ValidationModel->destroyApiSession(); //ui_changes
        if ($validate) {
            $response["success"] = TRUE;
            $response["message"] = "You have Successfully Logged Out!";
        } else {
            $response['error_type'] = 1;
        }
        echo json_encode($response);
    }

    public function socialSignup() {
        $response = array();
        $response['success'] = FALSE;
        $response['error_type'] = 0;
        $response['message'] = "Something Wrong";

        $email = trim($this->input->post('email')); /* Email */
        $token = trim($this->input->post('token')); /* token */
        $social_id = trim($this->input->post('social_id'));  /* Social ID */ // it will be fb or google ID

        $processFlag = true;

        if (!filter_email_domain($email)) {
            $response['error_type'] = 1;
            $response['message'] = "Please enter a valid Email ID.";
            $processFlag = false;
        } else if (!validateParam($token)) {
            $response['error_type'] = 1;
            $response['message'] = "Please enter a valid Token.";
            $processFlag = false;
        }
        if ($processFlag) {
            $response = social_token($token, $email);
            if (array_key_exists('social_id', $response)) {
                $social_id = $response['social_id'];
            } else {
                echo json_encode($response);
                exit;
            }

            $where = "email='$email'";
//Get User Data 
            $userData = $this->CommonModel->getRow("users", $where, "user_id,email,email_verified,google_id");

            if (!empty($userData)) {
                if (!empty($userData['google_id'])) {
                    $response = $this->generateToken($userData['user_id']);
                }
            } else {
                $register = array(
                    'email' => $email,
                    'google_id' => $social_id,
                    'created_date' => date('Y-m-d H:i:s'),
                );
                $userId = $this->CommonModel->add('users', $register);
                $response = $this->generateToken($userId);
            }
        }
        echo json_encode($response);
    }

    function generateToken($userId) {
        $data = [];

        $data['user_id'] = $userId;
//TOKEN
        $data['token'] = _guid();
        $data['updated_date'] = $data['created_date'] = date('Y-m-d H:i:s');
        $data['expired_date'] = date('Y-m-d H:i:s', strtotime('+168 hours')); //default token time

        $this->db->trans_start();

        $this->CommonModel->add('oauth_token', $data);
//        $this->db->set($data)->insert('oauth_token');

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $response = ['success' => FALSE, 'message' => 'Internal server error. try after some time', 'error_code' => 0, 'error_type' => 0];
        } else {
            $this->db->trans_commit(); //$this->db->trans_complete();
            $response["success"] = TRUE;
            $response["message"] = 'SUCCESS';
            $where = "user_id=$userId";
//Get User Data 
            $userData = $this->CommonModel->getRow("users", $where, "*");

            if (count($userData) > 0) {
                $userData['user_id'] = _encode($userData['user_id']);
            }

            $response["userData"] = $userData;
            $response['token'] = $data['token'];
            $response['client_service'] = 'mobile-client';
            $response['auth_key'] = 'reciperestapi';
        }
        return $response;
    }

    function addServiceKey() {
        $data = [];
//TOKEN
        $data['api_key'] = _guid();
        $data['updated_date'] = $data['created_date'] = date('Y-m-d H:i:s');
        $data['expired_date'] = date('Y-m-d H:i:s', strtotime('+1440 hours')); //default token time

        $this->CommonModel->add('service_token', $data);

        $response["success"] = TRUE;
        $response["message"] = 'SUCCESS';

        echo json_encode($response);
    }

}
