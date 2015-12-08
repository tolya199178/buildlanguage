<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class download extends Front_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $path = $_FILES['tmp_name'];
        set_include_path(dirname(__DIR__) . '/libraries/PHPExcel/');
        /** PHPExcel_IOFactory */
        include 'PHPExcel/IOFactory.php';
        $files = $_FILES['fileToUpload'];
        $inputFileName = $files['tmp_name'];
        if (is_file($inputFileName)) {

            /*             * *
             * Parse Excel File
             */
            $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
            $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);

            /**
             * Make Languge Data
             */
            $data = array();
            $hrow = array();
            foreach ($sheetData as $key => $row) {
                if ($key == 1) {
                    //First HeaderRow
                    $hrow = $row;
                    foreach ($row as $col => $value) {
                        if ($value && $col != 'A') {
                            $data[$value] = array();
                        }
                    }
                } else {
                    $key = '';
                    $defaultValue = '';
                    foreach ($row as $col => $value) {
                        $value = trim($value);
                        if ($col == 'A') {
                            if (!$value) {
                                break;
                            }
                            $key = $value;
                        } else {
                            $lang = $hrow[$col];
                            if ($lang == 'en') {
                                $defaultValue = $value;
                            }
                            if (is_array($data[$lang])) {
                                $data[$lang][$key] = $value ? $value : $defaultValue;
                            }
                        }
                    }
                }
            }

            /**
             * Download As Zip
             */
            $this->load->library('zip');
            foreach ($data as $languageKey => $keyData) {
                $fileName = sprintf('%s.json', $languageKey);
                $fieContent = json_encode($keyData);
                $this->zip->add_data($fileName, $fieContent);
            }
            $this->zip->download('language.zip');
        } else {
            die("No Selected File");
        }
    }

}
