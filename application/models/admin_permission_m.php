<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class admin_permission_m extends CI_Model {

    const T_NAME = 'admin_permissions';
    
    public function deletePermissionByUserId($userId){
        return $this->db->delete(self::T_NAME, array('admin_id' => $userId));
    }
    
    public function setPermission($userId, $permission){
        $ary = array();
        $ary['admin_id'] = $userId;
        $ary['permission'] = $permission;
        return $this->db->insert(self::T_NAME, $ary);
    }
    
    
    
    public function getUserPermissions($userId){
        $sql = sprintf(
                "SELECT admin_id,permission FROM %s WHERE admin_id='%d'", self::T_NAME, $userId
        );
        $query = $this->db->query($sql);
        $rows =  $query->result_array();
        $return = array();
        if(sizeof($rows) > 0){
            foreach($rows as $row){
                $return[] = $row['permission'];
            }
        }
        return $return;
    }
    
    
    public function getAllUserPermissions(){
        $sql = sprintf(
                "SELECT admin_id,permission FROM %s WHERE 1", self::T_NAME
        );
        $query = $this->db->query($sql);
        $rows =  $query->result_array();
        $return = array();
        if(sizeof($rows) > 0){
            foreach($rows as $row){
                if(isset($return[$row['admin_id']]) == false)
                    $return[$row['admin_id']]= array();
                $return[$row['admin_id']][] = $row['permission'];
            }
        }
        return $return;
    }
}
