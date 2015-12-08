<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class client_exit_strategy_m extends CI_Model {

    const T_NAME = 'client_exit_strategies';

    public function getValues($clientId) {
        $sql = "SELECT strategy_key AS value FROM " . self::T_NAME . " WHERE client_id='{$clientId}'";
        $query = $this->db->query($sql);
        $rows = $query->result_array();

        function col($a) {
            return $a['value'];
        }

        return array_map('col', $rows);
    }

    public function setValues($clientId, $values) {
        $this->db->delete(self::T_NAME, array('client_id' => $clientId));
        if (count($values) > 0) {
            foreach ($values as $val) {
                $this->db->insert(self::T_NAME, array('client_id' => $clientId, 'strategy_key' => $val));
            }
        }
    }

}
