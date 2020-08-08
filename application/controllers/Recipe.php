<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Recipe extends CI_Controller {

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
    public $user_id;

    function __construct() {
        parent::__construct();
        $this->user_id = _decode($this->input->get_request_header('ID', TRUE));
        $authApply = true;

        if ($this->uri->segment(2) == 'getRecipes') {
            $authApply = false;
        }
        if ($authApply == true) {
            required_field($this->user_id, 'user_id');
            if ($this->user_id == 0) {
                $response['success'] = false;
                $response['error_type'] = 0;
                $response['message'] = 'Invalid request.';
                echo json_encode($response);
                exit;
            }

            $response = $this->ValidationModel->validateToken();
            /* validate user request header. */
            if ($response['success'] === FALSE) {
                echo json_encode($response);
                exit;
            }
        }
    }

    public function getRecipes() {
        $response = array();
        $response['success'] = FALSE;
        $response['error_type'] = 0;
        $response['message'] = "Something Wrong";

        $processFlag = TRUE;

        if ($processFlag) {
//            $where = "email='$email'";
//Get User Data 
            $recipeData = $this->CommonModel->getRecords("recipe", '', "*", "", "created_date", "desc");
            if (!empty($recipeData)) {
                $response['success'] = TRUE;
                $response['message'] = "SUCCESS";
                for ($i = 0; $i < count($recipeData); $i++) {
                    $image_name = strtolower(str_replace(" ", "-", $recipeData[$i]['name']));
                    $recipeData[$i]['image'] = base_url() . "assets/images/" . $image_name . ".jpg";
                }
                $response["recipeData"] = $recipeData;
            } else {
                $response['error_type'] = 1;
                $response['message'] = "No Recipes Found";
            }
        }
        echo json_encode($response);
    }

    public function getRecipeData() {
        $response = array();
        $response['success'] = FALSE;
        $response['error_type'] = 0;
        $response['message'] = "Something Wrong";

        $recipeId = trim($this->input->post('recipeId')); /* Recipe ID */
        $processFlag = TRUE;
        if ($recipeId == '') {
            $response['error_type'] = 1;
            $response['message'] = "Please Enter Recipe ID";
            $processFlag = FALSE;
        }
        if ($processFlag) {
            $where = "recipe_id=$recipeId";
//Get User Data 
            $recipeData = $this->CommonModel->getRow("recipe", $where, "*");

            $image_name = strtolower(str_replace(" ", "-", $recipeData['name']));
            $recipeData['image'] = base_url() . "assets/images/" . $image_name . ".jpg";

            if (!empty($recipeData)) {
                $response['success'] = TRUE;
                $response['message'] = "SUCCESS";
                $response["recipeData"] = $recipeData;
            } else {
                $response['error_type'] = 1;
                $response['message'] = "No Recipe Data Found";
            }
        }
        echo json_encode($response);
    }

}
