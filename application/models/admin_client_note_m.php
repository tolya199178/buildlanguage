<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class admin_client_note_m extends CI_Model {

    const T_NAME = 'admin_client_notes';

    public function getClientNotes($clientId, $adminId = null) {
        $sql = "SELECT N.note_id, N.client_id, N.admin_id, N.note, N.created_time, A.first_name AS admin_first_name, A.last_name AS admin_last_name "
                . "FROM " . self::T_NAME . " AS N INNER JOIN administrators AS A ON N.admin_id=A.admin_id "
                . "WHERE client_id='{$clientId}'";
        if (!empty($adminId)) {
            $sql .= " AND admin_id='{$adminId}'";
        }
        $sql .= " ORDER BY created_time DESC";
        $query = $this->db->query($sql);
        $rows = $query->result_array();
        if (sizeof($rows) > 0) {
            $this->load->model('administrator_m', 'administratorModel');
            foreach ($rows as &$row) {
                $administartor = $this->administratorModel->getUserById($row['admin_id']);
                $row['admin_name'] = $administartor['first_name'] . " " . $administartor['last_name'];
            }
        }
        return $rows;
    }

    public function addNote($clientId, $note, $adminId = null) {
        if ($adminId == null)
            $adminId = $this->session->userdata('admin_id');
        //@todo
        //$adminId = 52;
        $ary = array();
        $ary['client_id'] = $clientId;
        $ary['admin_id'] = $adminId;
        $ary['note'] = $note;
        $ary['created_time'] = date('Y-m-d H:i:s');
        $this->db->insert(self::T_NAME, $ary);
        return $this->db->insert_id();
    }

    public function deleteClientNote() {
        
    }

}
