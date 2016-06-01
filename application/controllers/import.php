<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class import extends Front_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {


        $xlsFilePath = dirname(__DIR__) . '/files/lang.xlsx.xlsx';
        $outputxlsFilePath = dirname(__DIR__) . '/files/output.xlsx';
        $jsonFile = dirname(__DIR__) . '/files/de.json';

        $enCol = "B";
        $langCol = "F";
        if (is_file($jsonFile)) {
            $content = file_get_contents($jsonFile);
            $langData = (array) json_decode($content);
        }


        set_include_path(dirname(__DIR__) . '/libraries/PHPExcel/');
        /** PHPExcel_IOFactory */
        include 'PHPExcel/IOFactory.php';
        $files = $_FILES['fileToUpload'];

        if (is_file($xlsFilePath)) {

            /*             * *
             * Parse Excel File
             */
            $PHPExcel = PHPExcel_IOFactory::load($xlsFilePath);

            $sheetData = $PHPExcel->getActiveSheet()->toArray(null, true, true, true);

            $objReader = PHPExcel_IOFactory::createReader("Excel2007");
            $objPHPExcel = $objReader->load($xlsFilePath);

            foreach ($sheetData as $key => $row) {
                $enKey = $row[$enCol];
                if ($langData[$enKey]) {
                    if ($enKey != $langData[$enKey]) {
                        $value = $langData[$enKey];
                        $objPHPExcel->getActiveSheet()->setCellValue($langCol . $key, $value);
                    }
                }
            }

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save($outputxlsFilePath);
            die("ok");

            exit;
        } else {
            die("No Selected File");
        }
    }

}
