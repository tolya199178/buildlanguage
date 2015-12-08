<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class index extends Front_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->load->view('upload_form');
    }

}
