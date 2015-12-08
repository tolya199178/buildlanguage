<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class client_type_m extends CI_Model {

    const T_NAME = 'client_type';

    public function getValues($clientId = null) {
        $sql = "SELECT client_type AS value FROM " . self::T_NAME;
        if (isset($clientId)) {
            $sql .= " WHERE client_id='{$clientId}'";
        }

        $query = $this->db->query($sql);
        $rows = $query->result_array();
        if (!function_exists('col2f')) {

            function col2f($a) {
                return $a['value'];
            }

        }
        return array_map('col2f', $rows);
    }

    public function setValues($clientId, $values) {
        $this->db->delete(self::T_NAME, array('client_id' => $clientId));
        if (count($values) > 0) {
            foreach ($values as $val) {
                $this->db->insert(self::T_NAME, array('client_id' => $clientId, 'client_type' => $val));
            }
        }
    }

}
