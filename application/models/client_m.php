<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class client_m extends CI_Model {

    const T_NAME = 'clients';

    public function login($username, $password) {
        $sql = "SELECT client_id, username, first_name, last_name, email_addr, company_name, actived, status"
                . " FROM " . self::T_NAME . " WHERE username='{$username}' AND  passwd='" . md5($password) . "'";
        $query = $this->db->query($sql);
        if ($query->num_rows() == 0) {
            return false;
        }
        $resultAry = $query->row_array();
        if ($resultAry['actived']) {
            $resultAry['login_time'] = date('Y-m-d H:i:s');
            $this->db->query("UPDATE " . self::T_NAME . " SET last_login_time='" . $resultAry['login_time'] . "'"
                    . " WHERE client_id='{$resultAry['client_id']}'");
        }
        return $resultAry;
    }

    public function setClientSession($clientInfo) {
        $clientInfo['logged_in'] = true;
        $this->session->set_userdata($clientInfo);
    }

    public function getProfile($clientId) {
        $sql = "SELECT * FROM " . self::T_NAME . " WHERE client_id='{$clientId}'";
        $query = $this->db->query($sql);
        $clientInfo = $query->row_array();

        //get exit strategies
        $this->load->model('client_exit_strategy_m');
        $clientInfo['exit_strategies'] = $this->client_exit_strategy_m->getValues($clientId);

        //get financing methods
        $this->load->model('client_financing_method_m');
        $clientInfo['financing_methods'] = $this->client_financing_method_m->getValues($clientId);

        //get property types
        $this->load->model('client_property_type_m');
        $clientInfo['property_types'] = $this->client_property_type_m->getValues($clientId);

        //get interested areas
        $this->load->model('client_interested_area_m');
        $clientInfo['interested_areas'] = $this->client_interested_area_m->getValues($clientId);

        //get client types
        $this->load->model('client_type_m');
        $clientInfo['client_types'] = $this->client_type_m->getValues($clientId);

        return $clientInfo;
    }

    public function saveProfile($clientId, $info, $onefield = false) {
        $colNames = array('passwd', 'first_name', 'last_name', 'company_name', 'address', 'city', 'state', 'zip',
            'work_phone', 'fax', 'mobile_phone', 'email_addr', 'available_assets', 'purchase_price_range_from',
            'purchase_price_range_to', 'last_purchased_investment_numbers', 'comments', 'status', 'agent_name');

        $clientInfo = array();
        foreach ($colNames as $col) {
            if ($onefield) {
                if (isset($info[$col])) {
                    $clientInfo[$col] = $info[$col];
                }
            } else {
                if (!($col == 'passwd' && $info[$col] == '')) {
                    if ($col == 'passwd') {
                        $info[$col] = md5($info[$col]);
                    }
                    $clientInfo[$col] = $info[$col];
                }
            }
        }

        $updateSql = $this->db->update_string(self::T_NAME, $clientInfo, "client_id='{$clientId}'");
        $this->db->query($updateSql);

        //set exit strategies
        if ($onefield) {
            if (isset($info['exit_strategies'])) {
                $this->load->model('client_exit_strategy_m');
                $this->client_exit_strategy_m->setValues($clientId, $info['exit_strategies']);
            }
        } else {
            $this->load->model('client_exit_strategy_m');
            $this->client_exit_strategy_m->setValues($clientId, $info['exit_strategies']);
        }

        //set financing methods
        if ($onefield && $info['financing_methods']) {
            if (isset($info['financing_methods'])) {
                $this->load->model('client_financing_method_m');
                $this->client_financing_method_m->setValues($clientId, $info['financing_methods']);
            }
        } else {
            $this->load->model('client_financing_method_m');
            $this->client_financing_method_m->setValues($clientId, $info['financing_methods']);
        }

        //set property types
        if ($onefield) {
            if (isset($info['property_types'])) {
                $this->load->model('client_property_type_m');
                $this->client_property_type_m->setValues($clientId, $info['property_types']);
            }
        } else {
            $this->load->model('client_property_type_m');
            $this->client_property_type_m->setValues($clientId, $info['property_types']);
        }

        //set interested areas
        if ($onefield) {
            if (isset($info['interested_areas'])) {
                $this->load->model('client_interested_area_m');
                $this->client_interested_area_m->setValues($clientId, $info['interested_areas']);
            }
        } else {
            $this->load->model('client_interested_area_m');
            $this->client_interested_area_m->setValues($clientId, $info['interested_areas']);
        }

        //set client type
        if ($onefield) {
            if (isset($info['client_types'])) {
                $this->load->model('client_type_m');
                $this->client_type_m->setValues($clientId, $info['client_types']);
            }
        } else {
            $this->load->model('client_type_m');
            $this->client_type_m->setValues($clientId, $info['client_types']);
        }
        return true;
    }

    public function getClientBasicInfo($cliendId = null, $where = null) {
        $sql = sprintf(
                "SELECT client_id, username, first_name, last_name, email_addr, actived, company_name FROM %s WHERE 1", self::T_NAME
        );

        if ($where != '') {
            $sql .= ' AND ' . $where;
        }

        if ($cliendId != null) {
            $sql .= sprintf(" AND client_id='%d'", $cliendId);
            return $query = $this->db->query($sql)->row_array();
        } else {
            return $query = $this->db->query($sql)->result_array();
        }
    }

    public function getClientInfoByUsername($userName) {
        $sql = sprintf(
                "SELECT * FROM %s WHERE 1 AND username='%s'", self::T_NAME, $userName
        );
        return $this->db->query($sql)->result_array();
    }

    public function editaccount($clientId, $ary) {
        $this->db->update(self::T_NAME, $ary, array('client_id' => $clientId));
    }

    public function addInvestor($info) {
        $colNames = array('first_name', 'last_name', 'company_name', 'address', 'city', 'state', 'zip',
            'work_phone', 'fax', 'mobile_phone', 'email_addr', 'available_assets', 'purchase_price_range_from',
            'purchase_price_range_to', 'last_purchased_investment_numbers', 'comments');

        $clientInfo = array();
        foreach ($colNames as $col) {
            $clientInfo[$col] = $info[$col];
        }
        $clientInfo['created_time'] = date('Y-m-d H:i:s');
        if ($this->db->insert(self::T_NAME, $clientInfo) == false) {
            return false;
        }
        $clientId = $this->db->insert_id();
        //set exit strategies
        $this->load->model('client_exit_strategy_m');
        $this->client_exit_strategy_m->setValues($clientId, $info['exit_strategies']);

        //set financing methods
        $this->load->model('client_financing_method_m');
        $this->client_financing_method_m->setValues($clientId, $info['financing_methods']);

        //set property types
        $this->load->model('client_property_type_m');
        $this->client_property_type_m->setValues($clientId, $info['property_types']);

        //set interested areas
        $this->load->model('client_interested_area_m');
        $this->client_interested_area_m->setValues($clientId, $info['interested_areas']);

        return $clientId;
    }

    public function deleteInvestor($clientId) {
        return $this->db->delete(self::T_NAME, array('client_id' => $clientId));
    }

    public function getInvestors($params) {

        /* $lastSenderClientId = "SELECT IFNULL(sender_client_id, '') FROM "
          . "bid_messages WHERE sender_client_id=C.client_id OR to_client_id=C.client_id ORDER BY created_time DESC LIMIT 1"; */
        $tmpTable = "SELECT property_id, bid_id, MAX(last_created_time) AS last_created_time, client_id FROM ("
                . "(SELECT property_id, bid_id, MAX(created_time) AS last_created_time, sender_client_id AS client_id FROM bid_messages WHERE IFNULL(sender_client_id, '')<>'' GROUP BY property_id, bid_id, sender_client_id) "
                . "UNION (SELECT property_id, bid_id, MAX(created_time) AS last_created_time, to_client_id AS client_id FROM bid_messages WHERE IFNULL(to_client_id, '')<>'' GROUP BY property_id, bid_id, to_client_id) "
                . ") AS TINNER GROUP BY property_id, bid_id, client_id";
        $lastSenderClientId = "SELECT IFNULL(BM.sender_client_id, '') FROM "
                . "bid_messages AS BM INNER JOIN ({$tmpTable}) AS T "
                . "ON BM.created_time=T.last_created_time AND BM.property_id=T.property_id AND (T.client_id=BM.to_client_id OR T.client_id=BM.sender_client_id)"
                . "WHERE BM.sender_client_id=C.client_id OR BM.to_client_id=C.client_id ORDER BY BM.sender_client_id DESC LIMIT 1";

        // get Investor
        $sql = sprintf("SELECT *, IF(IFNULL(({$lastSenderClientId}), '')='', '', 1) AS has_unread_msg FROM %s AS C WHERE 1", self::T_NAME);

        if (!empty($params['name_search_str'])) {
            $sql .= sprintf(" AND (CONCAT(first_name,' ', last_name) LIKE '%%%s%%' OR CONCAT(first_name, last_name) LIKE '%%%s%%')", $params['name_search_str'], $params['name_search_str']);
        }
        if (!empty($params['client_id'])) {
            $sql .= " AND client_id='{$params['client_id']}'";
        }

        $sql .= " ORDER BY IFNULL(({$lastSenderClientId}), '') DESC";

        $sortingMap = array(
            'first_name' => 'first_name ASC',
            'last_name' => 'last_name ASC',
            'city' => 'city ASC',
            'state' => 'state ASC',
            'last_login_time' => 'last_login_time DESC',
            'most_bids' => '(SELECT COUNT(bid_id) FROM property_bids WHERE client_id=C.client_id) DESC',
        );


        if (isset($sortingMap[$params['sort_investors_by']])) {
            $sql .= "," . $sortingMap[$params['sort_investors_by']];
        } else {
            $sql .= "," . $sortingMap['first_name'];
        }

        $total_count = $this->db->query("SELECT COUNT(*) as total_count FROM ({$sql}) as a1")->row()->total_count;
        if ($total_count == 0) {
            return array('total_count' => 0,
                'start_point' => 0);
        }

        if ($params['page_num'] && $params['page_rows_count']) {
            if (($params['page_num'] - 1) * $params['page_rows_count'] > $total_count) {
                $startPoint = 0;
                $limit = $params['page_rows_count'];
            } else {
                $startPoint = ($params['page_num'] - 1) * $params['page_rows_count'];
                $limit = $params['page_rows_count'];
            }
            $sql .= " LIMIT {$startPoint}, {$limit}";
        }

        $rows = $this->db->query($sql)->result_array();

        $this->load->model('client_interested_area_m', 'clientInterestedAreaModel');
        $this->load->model('client_financing_method_m', 'clientFinancingMethodModel');
        $this->load->model('client_property_type_m', 'clientPropertyTypeModel');
        $this->load->model('admin_client_note_m', 'adminClientNoteModel');
        $this->load->model('propertyview_track_m', 'propertyviewTrackModel');
        $this->load->model('client_feedback_m', 'clientFeedbackModel');
        $this->load->model('favorite_m', 'favoriteModel');
        $this->load->model('note_m', 'noteModel');
        $this->load->model('bid_message_m');

        if (sizeof($rows) > 0) {
            foreach ($rows as &$row) {
                $client_id = $row['client_id'];

                //get insterest states
                $row['interest_states'] = $this->clientInterestedAreaModel->getInterestStates($client_id);

                //get financing Method
                $financingmethodes = $this->clientFinancingMethodModel->getValues($client_id);
                $row['financing_methods'] = $financingmethodes;

                //get property_type
                $propertyType = $this->clientPropertyTypeModel->getValues($client_id);
                $row['property_types'] = $propertyType;

                //get internal_notes
                //$internalnotes = $this->adminClientNoteModel->getClientNotes($client_id);
                //$row['internal_notes'] = $internalnotes;                
                // get visited propterty
                $vistied_property = $this->propertyviewTrackModel->getVistedProperties($client_id);
                $row['visit_properties'] = $vistied_property;

                //get client_feedbacks;
                //$clientfeedbacks = $this->clientFeedbackModel->getClientFeedbackss($client_id);
                //$row['client_feedbacks'] = $clientfeedbacks;
                //get favorites property
                $favoriteProerty = $this->favoriteModel->getFavoriteProperties($client_id);
                $row['favorites'] = $favoriteProerty;

                //get client note
                $clientNotes = $this->noteModel->getClientNote($client_id);
                $row['client_notes'] = $clientNotes;

                //check if there is unread msg
                /* $messages = $this->bid_message_m->getMessages(array('for_client_id' => $client_id));
                  $row['has_unread_msg'] = '';
                  if (count($messages) > 0 && !empty($messages[0]['sender_client_id'])) {
                  $row['has_unread_msg'] = '1';
                  } */
            }
        }

        return array(
            'total_count' => $total_count,
            'start_point' => $startPoint,
            'rows' => $rows
        );
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

    /**
     * 
     * @param type $userId
     * @return type
     */
    public function getClientByEmailId($emailId) {
        $sql = sprintf(
                "SELECT * FROM %s WHERE  email_addr = '%s'", self::T_NAME, $emailId
        );
        return $this->db->query($sql)->row_array();
    }

    public function saveRegister($registerInfo) {
        $this->db->insert(self::T_NAME, array('username' => $registerInfo['username'], 'status' => 0));
        $clientId = $this->db->insert_id();
        unset($registerInfo['username']);
        $this->saveProfile($clientId, $registerInfo);
    }

}
