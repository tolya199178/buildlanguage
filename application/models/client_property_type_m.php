<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class client_property_type_m extends CI_Model {

    const T_NAME = 'client_property_types';

    public function getValues($clientId){
        $sql = "SELECT type_key AS value FROM ".self::T_NAME." WHERE client_id='{$clientId}'";
        $query = $this->db->query($sql);
        $rows = $query->result_array();
        if(!function_exists(col3)){
         function col3($a)
            {
                return $a['value'];
            }   
        }        
        return array_map('col3', $rows);
    }
    
    public function setValues($clientId, $values) {
        $this->db->delete(self::T_NAME, array('client_id' => $clientId));
        if (count($values) > 0) {
            foreach ($values as $val) {
                $this->db->insert(self::T_NAME, array('client_id' => $clientId, 'type_key' => $val));
            }
        }
    }
}
