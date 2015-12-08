<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class client_feedback_m extends CI_Model {

   const T_NAME = 'client_feedbacks';

   public function getClientFeedbackss($clientId){
        $sql = "SELECT feedback_d, admin_id, feedback,created_time FROM ".self::T_NAME." WHERE client_id='{$clientId}' ORDER BY created_time DESC";
        $query = $this->db->query($sql);
        $rows = $query->result_array();
        if(sizeof($rows) > 0){
            $this->load->model('administrator_m', 'administratorModel');
            foreach($rows as &$row){
                $administartor = $this->administratorModel->getUserById($row['admin_id']);
                $row['admin_name'] = $administartor['first_name'] ." ". $administartor['last_name'];
            }
        }
        return $rows;
   }
   
   public function addFeedback($clientId, $feedback, $adminId = null) {
        if ($adminId == null)
            $adminId = $this->session->userdata('admin_id');
        //@todo
        //$adminId = 52;
        $ary = array();
        $ary['client_id'] = $clientId;
        $ary['admin_id'] = $adminId;
        $ary['feedback'] = $feedback;
        $ary['created_time'] = date('Y-m-d H:i:s');
        $this->db->insert(self::T_NAME, $ary);
        return $this->db->insert_id();
    }
}
