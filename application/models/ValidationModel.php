<?php

/* Shankar Panaskar */
defined('BASEPATH') or exit('No direct script access allowed');

class ValidationModel extends CI_Model {

    public $client_service = CLIENT_SERVICE_KEY;
    public $auth_key = AUTH_KEY;

    //used for logout delete keys
    public function destroyApiSession() { //oauth->signout
        $user_id = _decode($this->input->get_request_header('ID', TRUE));
        $token = $this->input->get_request_header('Authorization', TRUE);
        $this->db->where('user_id', $user_id)->where('token', $token)->delete('oauth_token');
        return TRUE;
    }

    public function validateToken() {
        $response = [];
        $response['success'] = FALSE;

        $client_service = $this->input->get_request_header('Client-Service', TRUE);
        $auth_key = $this->input->get_request_header('Auth-Key', TRUE);
        $user_id = _decode($this->input->get_request_header('ID', TRUE));
        $token = $this->input->get_request_header('Authorization', TRUE);

        if ($client_service == $this->client_service && $auth_key == $this->auth_key) {
            $this->db->select('email_verified');
            $this->db->from('users');
            $this->db->where('user_id', $user_id);
            $get_user = $this->db->get()->row();
            if (count($get_user) != 0) {
                if ($get_user->email_verified == 0) {
                    $response['message'] = "Your Account is Deleted / Blocked,Please Contact PlayerzPot Support";
                    $response['error_code'] = 3;
                    $response['error_type'] = 3;
                } else {
                    $this->db->select('expired_date');
                    $this->db->from('oauth_token');
                    $this->db->where('user_id', $user_id);
                    $this->db->where('token', $token);

                    $tokenCheck = $this->db->get()->row();
                    if (count($tokenCheck) == 0) {
                        $response['message'] = 'Invalid request';
                        $response['error_code'] = 2;
                        $response['error_type'] = 2;
                    } else {
                        if (strtotime($tokenCheck->expired_date) < strtotime(date('Y-m-d H:i:s'))) {
                            $response['message'] = 'Your session has been expired.';
                            $response['error_code'] = 2;
                            $response['error_type'] = 2;
                        } else {
                            $response['success'] = TRUE;
                        }
                    }
                }
            } else {
                $response['message'] = 'Invalid request';
                $response['error_code'] = 2;
                $response['error_type'] = 2;
            }
        } else {
            $response['message'] = 'Unauthorized.';
            $response['error_code'] = 3;
            $response['error_type'] = 2;
        }
        return $response;
    }

    public function validateKey() {
        $response = [];
        $response['success'] = FALSE;

        $client_service = $this->input->get_request_header('Client-Service', TRUE);
        $auth_key = $this->input->get_request_header('Auth-Key', TRUE);
        $key = $this->input->get_request_header('API-KEY', TRUE);

        if ($client_service == $this->client_service && $auth_key == $this->auth_key) {
            $this->db->select('expired_date');
            $this->db->from('service_token');
            $this->db->where('is_delete', 0);
            $this->db->where('api_key', $key);

            $tokenCheck = $this->db->get()->row();
            if (count($tokenCheck) == 0) {
                $response['message'] = 'Invalid request';
                $response['error_code'] = 2;
                $response['error_type'] = 2;
            } else {
                if (strtotime($tokenCheck->expired_date) < strtotime(date('Y-m-d H:i:s'))) {
                    $response['message'] = 'Your service has been expired.';
                    $response['error_code'] = 2;
                    $response['error_type'] = 2;
                } else {
                    $response['success'] = TRUE;
                }
            }
        } else {
            $response['message'] = 'Unauthorized.';
            $response['error_code'] = 3;
            $response['error_type'] = 2;
        }
        return $response;
    }

    public function validateHeader() { //Oauth
        $response = [];
        $response['success'] = FALSE;
        $client_service = $this->input->get_request_header('Client-Service', TRUE);
        $auth_key = $this->input->get_request_header('Auth-Key', TRUE);
        if ($client_service === $this->client_service && $auth_key === $this->auth_key) {
            $response['success'] = TRUE;
        } else {
            $response['message'] = 'Unauthorized.';
            $response['error_code'] = 3;
            $response['error_type'] = 2;
        }
        return $response;
    }

}
