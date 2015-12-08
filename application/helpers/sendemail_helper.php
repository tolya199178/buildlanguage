<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
if (!function_exists('sendActiveEmail')) {

    function sendActiveEmail($param) {
        $CI = & get_instance();
        $CI->load->library('email');
        $config['protocol'] = 'smtp';
        $config['smtp_host'] = 'smtp.udag.de';
        $config['smtp_port'] = '25';
        $config['smtp_timeout'] = '7';
        $config['smtp_user'] = 'unser-menu-0001';
        $config['smtp_pass'] = '5134134134AbA';
        $config['charset'] = 'utf-8';
        $config['newline'] = "\r\n";
        $config['mailtype'] = 'html'; // or html
        $config['validation'] = TRUE; // bool whether to validate email or not     

        $CI->email->initialize($config);
        $CI->load->library('email');

        $CI->email->from($param['email'], $param['name']);
        $CI->email->to($param['toaddress']);

        $CI->email->subject('Contact Mail');
        $CI->email->message($param['message']);
        $CI->email->send();
        echo $CI->email->print_debugger();
    }

}
