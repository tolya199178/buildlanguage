<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class company_m extends CI_Model {

    const T_NAME = 'companies';
   
    public function getAllCompayNames(){
        $sql = sprintf(
                "SELECT company_id,company_name FROM %s WHERE 1", self::T_NAME
        ); 
        $query = $this->db->query($sql);
        $rows = $query->result_array();
        $return = array();
        if(sizeof($rows) > 0){
            foreach($rows as $row){
                $return[$row['company_id']] = $row['company_name'];
            }
        }
        return $return;
    }
    
    public function getCompanyById($companyId){
        $sql = "SELECT * FROM ".self::T_NAME." WHERE company_id='{$companyId}'";
        $query = $this->db->query($sql);
        return $query->row_array();
    }
}
