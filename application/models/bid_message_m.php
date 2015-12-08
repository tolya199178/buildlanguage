<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class bid_message_m extends CI_Model {

    const T_NAME = 'bid_messages';

    /**
     * @param array $params
     *      bid_id
     * 
     * @return array
     *      total_count
     *      rows array
     */
    public function getMessages($params) {

        $sql = "SELECT * FROM " . self::T_NAME . " WHERE 1";

        if (!empty($params['bid_id'])) {
            $sql .= " AND bid_id='{$params['bid_id']}'";
        }

        if (!empty($params['for_client_id'])) {
            $sql .= " AND (sender_client_id='{$params['for_client_id']}' OR to_client_id='{$params['for_client_id']}')";
        }
        
        if (!empty($params['sender_client_id'])) {
            $sql .= " AND sender_client_id='{$params['sender_client_id']}'";
        }

        if (!empty($params['property_id'])) {
            $sql .= " AND property_id='{$params['property_id']}'";
        }
        
        
        $sql .= " ORDER BY created_time DESC";

        $query = $this->db->query($sql);

        return $rows = $query->result_array();
    }

    /* @refer columns of this table for $msg structure. */
    public function sendMessage($msg) {
        if (empty($msg['created_time'])) {
            $msg['created_time'] = date('Y-m-d H:i:s');
        }
        
        $msg['msg_content'] = nl2br($msg['msg_content']);

        return $this->db->insert(self::T_NAME, $msg);
    }
    
    public function getClientNonBidProperties($clientId){
        $hasUnread = "SELECT IFNULL(sender_client_id, '') FROM "
                . "bid_messages "
                . "WHERE IFNULL(bid_id, '')='' AND property_id=P.property_id AND (sender_client_id='{$clientId}' OR to_client_id='{$clientId}') ORDER BY created_time DESC LIMIT 1";
        $hasUnread = "IF(IFNULL(({$hasUnread}), '')='', '', '1')";
                
        $sql = "SELECT P.*, {$hasUnread} AS has_unread_msg FROM bid_messages AS M INNER JOIN property_listings AS P ON M.property_id=P.property_id "
                . "WHERE IFNULL(M.bid_id, '')='' AND (M.sender_client_id='{$clientId}' OR M.to_client_id='{$clientId}') "
                . "GROUP BY M.property_id ORDER BY {$hasUnread} DESC, P.created_time DESC";
        $query = $this->db->query($sql);
        return $query->result_array();
    }
    
    public function getClientMsgBids($clientId){
        $hasUnread = "SELECT IFNULL(sender_client_id, '') FROM "
                . "bid_messages "
                . "WHERE IFNULL(bid_id, '')<>'' AND bid_id=B.bid_id AND (sender_client_id='{$clientId}' OR to_client_id='{$clientId}') ORDER BY created_time DESC LIMIT 1";
        $hasUnread = "IF(IFNULL(({$hasUnread}), '')='', '', '1')";
        
        $sql = "SELECT B.*, {$hasUnread} AS has_unread_msg FROM bid_messages AS M INNER JOIN property_bids AS B ON M.bid_id=B.bid_id "
                . "WHERE M.sender_client_id='{$clientId}' OR M.to_client_id='{$clientId}' "
                . "GROUP BY M.bid_id ORDER BY {$hasUnread} DESC, B.created_time DESC";
        $query = $this->db->query($sql);
        return $query->result_array();
    }
}
