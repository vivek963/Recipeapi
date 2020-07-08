<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function validateParam($val) {
    if (!empty(trim($val)))
        return TRUE;
    else
        return FALSE;
}

//Validate email Domain
function filter_email_domain($email) {
//$email = '11@mail-temp.com';
    $domain_array = explode("@", $email);
    $domain = array_pop($domain_array);
    $disallowed_domains = ['getbreathtaking.com', 'mailfile.net', 'sdmcujire.in', 'gmial.com', 'zetmail.com', 'gmil.com', 'tuofs.com', 'gmai.com', 'getnada.com', 'mail-line.net', 'mail-guru.net', 'mail-desk.net', 'mail-line.net', 'kpay.be', 'honeys.be', 'mbox.re', 'kmail.li', 'kkmail.be', 'prin.be', 'rapt.be', 'bcaoo.com', 'rich-mail.net', 'yevme.com', 'givmail.com', 'mail-temp.com', 'yopmail.com', 'dispostable.com'];
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if (checkdnsrr($domain, "MX") && !in_array($domain, $disallowed_domains)) {
//echo 'Valid Email';
            return true;
        } else {
//echo 'Invalid Domain';
            return false;
        }
    } else {
//echo 'Invalid Email';
        return false;
    }
}

function _encode($value) {
    if (!$value) {
        return false;
    }
    return trim(_safe_b64encode($value));
}

function _decode($value) {
    if (!$value) {
        return false;
    }
    return trim(_safe_b64decode($value));
}

//generate access token & api key
function _guid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
// four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
// 8 bits for "clk_seq_low",
// two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function generateRandom($length = 6) {
    return rand(100000, 999999);
}

function social_token($token, $email) {
    $response['success'] = false;
    $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . $token;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $res = curl_exec($ch);
    curl_close($ch);
    $decode_data = json_decode($res, TRUE);
    if (array_key_exists('sub', $decode_data) && array_key_exists('email', $decode_data)) {
        $email_id_res = $decode_data['email'];
        if ($email_id_res == $email) {
            $response['social_id'] = $decode_data['sub'];
        }
    } else if (array_key_exists('error', $decode_data)) {
        $response['error_type'] = 1;
        $response['message'] = "Email ID error! Try another Email ID.";
    } else {
//user has not granted the "profile" or "email"
        $response['error_type'] = 1;
        $response['message'] = "Email ID error! Try another Email ID.";
    }
    return $response;
}
