<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class client_interested_area_m extends CI_Model {

    const T_NAME = 'client_interested_areas';

    public function getValues($clientId) {
        $sql = "SELECT state, county FROM " . self::T_NAME . " WHERE client_id='{$clientId}'";
        $query = $this->db->query($sql);
        $rows = $query->result_array();

        function col_area($a) {
            return $a['state'] . ':' . $a['county'];
        }

        return array_map('col_area', $rows);
    }

    public function setValues($clientId, $values) {
        $this->db->delete(self::T_NAME, array('client_id' => $clientId));
        if (count($values) > 0) {
            foreach ($values as $state => $counties) {
                if (count($counties) > 0) {
                    foreach ($counties as $county) {
                        $this->db->insert(self::T_NAME, array('client_id' => $clientId, 'state' => $state, 'county' => $county));
                    }
                }
            }
        }
    }
    
    public function getInterestArea($clientId){
        $sql = "SELECT state, county FROM " . self::T_NAME . " WHERE client_id='{$clientId}'";
        $query = $this->db->query($sql);
        $rows = $query->result_array();
        $ary = array();
        if(sizeof($rows) > 0){
            foreach($rows as $row){
                $ary[] = $row['county'];
            }            
        }
        return $ary;
    }
 
    public function getInterestStates($clientId){
        $sql = "SELECT DISTINCT(state) AS state FROM " . self::T_NAME . " WHERE client_id='{$clientId}'";
        $query = $this->db->query($sql);
        $rows = $query->result_array();
        $ary = array();
        if(sizeof($rows) > 0){
            foreach($rows as $row){
                $ary[] = $row['state'];
            }            
        }
        return $ary;
    }

}
