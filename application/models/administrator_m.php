<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class administrator_m extends CI_Model {

    const T_NAME = 'administrators';

    /**
     * @decription  
     * @param type $username
     * @param type $password
     * @return boolean
     */
    public function login($username, $password) {

        $sql = sprintf(
                "SELECT admin_id, username, first_name, last_name, company_id, email_addr, email_addr2,is_super FROM %s WHERE username='%s' AND passwd='%s'", self::T_NAME, mysql_real_escape_string($username), md5($password)
        );
        $query = $this->db->query($sql);
        if ($query->num_rows() == 0) {
            return false;
        }
        $resultAry = $query->row_array();
        $resultAry['login_time'] = date('Y-m-d H:i:s');
        $this->db->query("UPDATE " . self::T_NAME . " SET last_login_time='" . $resultAry['login_time'] . "'"
                . " WHERE admin_id='{$resultAry['admin_id']}'");
        return $resultAry;
    }

    public function setAdminSession($ary) {
        $this->session->set_userdata(array(
            'admin_id' => $ary['admin_id'],
            'admin_username' => $ary['username'],
            'admin_first_name' => $ary['first_name'],
            'admin_last_name' => $ary['last_name'],
            'is_super' => (int) $ary['is_super'] == 1,
            'admin_login_time' => date('Y-m-d H:i:s')
        ));
        $this->load->model('admin_permission_m', 'adminPermissionModel');
        $this->session->set_userdata('roles', $this->adminPermissionModel->getUserPermissions($ary['admin_id']));
    }

    /**
     * 
     * @param type $userId
     * @return type
     */
    public function getUserById($userId) {
        $sql = sprintf(
                "SELECT admin_id, username, first_name, last_name, company_id, email_addr, email_addr2, is_super FROM %s WHERE 1 AND admin_id='%d'", self::T_NAME, $userId
        );
        return $this->db->query($sql)->row_array();
    }

    /**
     * 
     * @param type $userId
     * @return type
     */
    public function getUserByEmailId($emailId) {
        $sql = sprintf(
                "SELECT admin_id, username, first_name, last_name, company_id, email_addr, email_addr2, is_super FROM %s WHERE  email_addr = '%s' or email_addr2 = '%s'", self::T_NAME, $emailId, $emailId
        );
        return $this->db->query($sql)->row_array();
    }

    /**
     * 
     * @param type $userData
     */
    public function insertUser($userData) {
        $userData['passwd'] = md5($userData['passwd']);
        $userData['created_time'] = date('Y-m-d H:i:s');
        $this->db->insert(self::T_NAME, $userData);
        return $this->db->insert_id();
    }

    public function updateUser($userId, $userData) {
        if ($userData['passwd'])
            $userData['passwd'] = md5($userData['passwd']);
        $this->db->update(self::T_NAME, $userData, array('admin_id' => $userId));
        return true;
    }

    public function deleteUser($userId) {
        return $this->db->delete(self::T_NAME, array('admin_id' => $userId));
    }

    public function isSupperUser($userId) {
        $user = $this->getUserById($userId);
        return $user['is_super'] == 1;
    }

    /**
     * 
     * @param type $userName
     * @return type
     */
    public function getUserByName($userName) {
        $sql = sprintf(
                "SELECT admin_id, username, first_name, last_name, company_id, email_addr, email_addr2, is_super FROM %s WHERE 1 AND username='%s'", self::T_NAME, mysql_real_escape_string($userName)
        );
        return $this->db->query($sql)->row_array();
    }

    /**
     * 
     * @param type $userId
     */
    public function getUserFullInfo($userId) {
        $row = $this->getUserById($userId);
        $this->load->model('admin_permission_m', 'adminPermission');
        $userRoles = $this->adminPermission->getUserPermissions($userId);
        if (is_array($userRoles) == false)
            $userRoles = array();
        $allRoles = get_admin_roles();
        $role = "";
        if ($row['is_super']) {
            $role = 'supper';
            $type = ucfirst(get_supper_admin_rolename());
        } else if (identical_values($allRoles, $userRoles)) {
            $role = "all";
            $type = ucfirst(get_allinclude_rolename());
        } else {
            $role = implode(',', $userRoles);
            foreach ($userRoles as &$item) {
                $item = ucfirst($item);
            };
            $type = implode(',', $userRoles);
        }
        $row = array_merge($row, array('role' => $role, 'usertype' => $type));
        return $row;
    }

    public function getAllUsers() {
        $sql = sprintf(
                "SELECT admin_id, username, first_name, last_name, company_id, email_addr, email_addr2, is_super FROM %s WHERE 1", self::T_NAME
        );
        $query = $this->db->query($sql);
        $this->load->model('admin_permission_m', 'adminPermission');
        $permissions = $this->adminPermission->getAllUserPermissions();
        $rows = $query->result_array();
        if (sizeof($rows) > 0) {
            $return = array();
            $allRoles = get_admin_roles();
            foreach ($rows as $row) {
                $userRoles = $permissions[$row['admin_id']];
                if (is_array($userRoles) == false)
                    $userRoles = array();
                $role = "";
                if ($row['is_super']) {
                    $role = get_supper_admin_rolename();
                    $type = ucfirst($role);
                } else if (identical_values($allRoles, $userRoles)) {
                    $role = get_allinclude_rolename();
                    $type = ucfirst($role);
                } else {
                    $role = implode(',', $userRoles);
                    foreach ($userRoles as &$item) {
                        $item = ucfirst($item);
                    }
                    $type = implode(',', $userRoles);
                }
                $return[] = array_merge($row, array('role' => $role, 'usertype' => $type));
            }
            return $return;
        } else {
            return array();
        }
    }

    public function getLastUpateTime() {
        $sql = sprintf(
                "SELECT created_time FROM %s WHERE 1  ORDER BY created_time DESC LIMIT 0,1", self::T_NAME
        );
        $row = $this->db->query($sql)->row_array();
        if (sizeof($row) > 0)
            return $row['created_time'];
        else
            return '';
    }

}
