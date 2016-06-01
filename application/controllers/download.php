<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class download extends Front_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $files = $_FILES['fileToUpload'];
        $inputFileName = $files['tmp_name'];
        if (is_file($inputFileName)) {
            /**
             * Parse Csv Data
             */
            $csvFile = file($inputFileName);
            $csvData = [];
            foreach ($csvFile as $line) {
                $csvData[] = str_getcsv($line);
            }

            /**
             * Make Language Data
             */
            $hrow = array();
            foreach ($csvData as $key => $row) {
                if ($key < 6) {
                    continue;
                } else if ($key == 6) {
                    //First HeaderRow
                    $hrow = $row;
                    foreach ($row as $col => $value) {
                        if ($value && $col != 0) {
                            $data[$value] = array();
                        }
                    }
                } else {
                    $key = '';
                    $defaultValue = '';
                    foreach ($row as $col => $value) {
                        $value = trim($value);
                        if ($col == 0) {
                            if (!$value) {
                                break;
                            }
                            $key = $value;
                            $defaultValue = $value;
                        } else {
                            $lang = $hrow[$col];
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
                $langKey = explode(",", $languageKey);
                $langCode = explode("-", trim($langKey[1]));
                $lang = "";
                if ($langCode[0] == "en") {
                    $lang = "en";
                } else {
                    $lang = $langCode[1];
                }

                $fileName = sprintf('%s.json', strtolower($lang));
                $fieContent = json_encode($keyData);
                $this->zip->add_data($fileName, $fieContent);
            }
            $this->zip->download('language.zip');
        } else {
            die("No Selected File");
        }
    }

}
