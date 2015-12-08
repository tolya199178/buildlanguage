<?php

function getBaseUrl() {
    if (PHP_SAPI == 'cli') {
        $trace = debug_backtrace();
        $relativePath = substr(dirname($trace[0]['file']), strlen(dirname(dirname(__FILE__))));
        echo "Warning: This sample may require a server to handle return URL. Cannot execute in command line. Defaulting URL to http://localhost$relativePath \n";
        return "http://localhost" . $relativePath;
    }
    $protocol = 'http';
    if ($_SERVER['SERVER_PORT'] == 443 || (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on')) {
        $protocol .= 's';
    }
    $host = $_SERVER['HTTP_HOST'];
    $request = $_SERVER['PHP_SELF'];
    return dirname($protocol . '://' . $host . $request);
}

function send_email($options) {
    $emailContent = $options['content'];
    if (!EMAIL_SENDABLE) {
        $filePath = APPPATH . '/logs/' . date('Y-m-d') . '.txt';
        $fp = fopen($filePath, 'a+');
        fwrite($fp, $options['subject'] . '(To: ' . $options['to'] . ')' . PHP_EOL . PHP_EOL . $emailContent . PHP_EOL
                . '=========================================================================' . PHP_EOL . PHP_EOL . PHP_EOL);
        fclose($fp);
        return;
    }
    $CI = & get_instance();

    $CI->load->library('email');

    $CI->email->clear();
    $CI->email->initialize(array(
        'mailtype' => 'html'
    ));
    $CI->email->from('register@hmcassets.com', "HMC Assets");
    $CI->email->reply_to('hubbs@hmcassets.com', "HMC Assets");
    $CI->email->to($options['to']);
    if (isset($options['cc'])) {
        $CI->email->cc($options['cc']);
    }
    if (isset($options['bcc'])) {
        $CI->email->bcc($options['bcc']);
    }
    $CI->email->subject($options['subject']);
    $CI->email->message($emailContent);
    $CI->email->send();
}
