<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class ServiceRecipe extends CI_Controller {

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
        $response = $this->ValidationModel->validateKey();
        /* validate user request header. */
        if ($response['success'] === FALSE) {
            echo json_encode($response);
            exit;
        }
    }

    public function getRecipes() {
        $response = array();
        $response['success'] = FALSE;
        $response['error_type'] = 0;
        $response['message'] = "Something Wrong";

        $processFlag = TRUE;

        if ($processFlag) {
            $where = "email='$email'";
//Get User Data 
            $recipeData = $this->CommonModel->getRecords("recipe", '1', "*");

            if (!empty($recipeData)) {
                $response['success'] = TRUE;
                $response['message'] = "SUCCESS";
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

        if ($processFlag) {
            $where = "recipe_id=$recipeId";
//Get User Data 
            $recipeData = $this->CommonModel->getRows("recipe", $where, "*");

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
