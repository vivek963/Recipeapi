<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

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
        $this->load->library('sendinblue/Mailin');
    }

    public function index() {
        $this->load->view('welcome_message');
    }

    function mailCron() {
        $today = date('Y-m-d H:i');
        $timeBefore1Hour = date('Y-m-d H:i', strtotime('-1 hour', strtotime($today)));
        $start = $timeBefore1Hour . ':00';
        $end = $timeBefore1Hour . ':59';

        $where = "(created_date >= '$start' AND created_date <= '$end') AND email_verified = 1";
//Get User Data 
        $recipeData = $this->CommonModel->getRecords("users", $where, "user_id,email,name", "", "created_date", "asc");
        if (count($recipeData) > 0) {
            for ($i = 0; $i < count($recipeData); $i++) {
                $email = $recipeData[$i]['email'];
                $name = $recipeData[$i]['name'];
                $mailin = new Mailin('https://api.sendinblue.com/v2.0', 'IPMmHnOTxjahF63Q');
                $subject = "Welcome Message";
                $message = "<p><strong><span style='font-size: 22px;'>Hi $name,</span></strong></p>
<p><span style='font-size: 18px;'>
Welcome!. Thank you for signing up with the Recipe. we hope you enjoy time with us.</span></p>
<p><span style='font-size: 18px;'>If you have any question.kindly contact us. we are always happy to help out. </span></p>
<p><span style='font-size: 18px;'>cheers,</span></p>
<p><span style='font-size: 18px;'>RecipeTeam,</span></p>";
                $mail_data = array(
                    "to" => [$email => ''],
                    "cc" => [],
                    "bcc" => [],
                    "from" => array("recipesbookss@gmail.com", "Recipe"),
                    "replyto" => array("recipesbookss@gmail.com", "Recipe Support Team"),
                    "subject" => $subject,
                    "text" => "",
                    "html" => $message,
                    "attachment" => array(),
                    "headers" => array("Content-Type" => "text/html; charset=iso-8859-1", "X-param1" => "value1", "X-param2" => "value2", "X-Mailin-custom" => "my custom value", "X-Mailin-IP" => "213.32.159.50", "X-Mailin-Tag" => "My tag")
                );
                $mailin->send_email($mail_data);
            }
        }
    }

}
