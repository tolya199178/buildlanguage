<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class property_bid_m extends CI_Model {

    const T_NAME = 'property_bids';

    public function submitBid($bidInfo) {
        if (empty($bidInfo['client_id'])) {
            $bidInfo['client_id'] = $this->session->userdata('client_id');
        }

        //generate bid code
        $this->load->model('property_m');
        $propertyInfo = $this->property_m->getPropertyinfo($bidInfo['property_id']);
        $bidInfo['bid_code'] = '';
        $propertyInfo['sales_type'] = strtolower($propertyInfo['sales_type']);
        if (strpos($propertyInfo['sales_type'], 'reo') !== false) {
            $bidInfo['bid_code'] = 'REO';
        } else if (strpos($propertyInfo['sales_type'], 'short_sales') !== false) {
            $bidInfo['bid_code'] = 'SS';
        } else if (strpos($propertyInfo['sales_type'], 'standard') !== false) {
            $bidInfo['bid_code'] = 'STD';
        } else {
            $bidInfo['bid_code'] = 'BID';
        }
        $bidInfo['bid_code'] .= str_pad($propertyInfo['npn_id'], 6, '0', STR_PAD_LEFT);
        $bidInfo['bid_code'] .= '-' . str_pad($bidInfo['client_id'], 6, '0', STR_PAD_LEFT);
        $bidInfo['bid_code'] .= '-' . date('mdy-His');

        $bidInfo['p_closing_date'] = date('Y-m-d', strtotime($bidInfo['p_closing_date']));
        $bidInfo['created_time'] = date('Y-m-d H:i:s');

        $this->db->insert(self::T_NAME, $bidInfo);

        $newBidId = $this->db->insert_id();
        
        //move non-bid messages to communication box
        $sql = "UPDATE bid_messages SET bid_id='{$newBidId}' WHERE property_id='{$bidInfo['property_id']}' AND (sender_client_id='{$bidInfo['client_id']}' OR to_client_id='{$bidInfo['client_id']}')";
        $this->db->query($sql);
        
        
        return $newBidId;
    }

    public function getClientPropertyBids($params) {
        if (empty($params['client_id'])) {
            $params['client_id'] = $this->session->userdata('client_id');
        }

        $sql = "SELECT * FROM " . self::T_NAME . " WHERE client_id='{$params['client_id']}' AND property_id='{$params['property_id']}' ORDER BY bid_code";

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /**
     * ***************************following methods are @called in admin pages**********************************
     */
    private function _getBidSelect($params) {
        $sql = "SELECT B.bid_id, B.client_id, P.property_id, B.offer_price, B.created_time AS bid_time "
                . "FROM  property_listings AS P "
                . "LEFT JOIN " . self::T_NAME . " AS B ON B.property_id=P.property_id WHERE 1 ";

        if (!empty($params['viewtype'])) {
            switch ($params['viewtype']) {
                case 'response_required':
                    //$lastSenderAdminId = "IFNULL((SELECT sender_admin_id FROM bid_messages WHERE bid_id=B.bid_id ORDER BY created_time DESC LIMIT 1), '')";
                    //$sql .= " AND " . $lastSenderAdminId . "='' AND B.bid_status IN ('opened', 'countered') AND IFNULL(P.closed_time, '')=''";
                    $sql .= " AND B.bid_status='opened'";
                    break;
                case 'since_inception':
                    //$adminResponseCount = "(SELECT COUNT(message_id) FROM bid_messages WHERE bid_id=B.bid_id AND IFNULL(sender_admin_id, '')<>'')";
                    //$sql .= " AND B.bid_status<>'closed' AND " . $adminResponseCount . ">0 AND IFNULL(P.closed_time, '')=''";
                    $sql .= " AND IFNULL(B.bid_id, '')<>''";
                    break;
                case 'countered':
                    $sql .= " AND B.bid_status='countered' AND IFNULL(P.closed_time, '')=''";
                    break;
                case 'accepted':
                    $sql .= " AND B.bid_status='accepted' AND IFNULL(P.closed_time, '')=''";
                    break;
                case 'declined':
                    $sql .= " AND B.bid_status='declined' AND IFNULL(P.closed_time, '')=''";
                    break;
                case 'lost':
                    $sql .= " AND B.bid_status<>'closed' AND IFNULL(P.closed_time, '')<>''";
                    break;
                case 'winning_lost_marketoff':
                    $sql .= " AND B.bid_status='accepted' AND IFNULL(P.closed_time, '')<>'' AND IFNULL(P.closed_type, '')='deleted'";
                    break;
                case 'assets_escrow':
                    $sql .= " AND P.sales_type='short_sales_escrow'";
                    break;
                case 'assets_sold':
                    $sql .= " AND P.sales_type='short_sales_sold'";
                    break;
                case 'sold':
                    $sql .= " AND B.bid_status='closed' AND IFNULL(P.closed_time, '')<>'' AND IFNULL(P.closed_type, '')='sold'";
                    break;
            }
        }

        if (!empty($params['admin_company'])) {
            switch (strtolower($params['admin_company'])) {
                case 'wedgewood':
                    $sql .= " AND LOCATE('reo', P.sales_type)=1";
                    break;
                case 'hmc':
                    $sql .= " AND (LOCATE('short_sales', P.sales_type)=1 OR LOCATE('standard_sales', P.sales_type)=1)";
                    break;
            }
        }

        if (!empty($params['property_id'])) {
            $sql .= " AND B.property_id='{$params['property_id']}'";
        }

        if (!empty($params['client_id'])) {
            $sql .= " AND B.client_id='{$params['client_id']}'";
        }

        return $sql;
    }

    //get properties that contains matched bids
    public function getProperties($params) {
        $bidSelect = $this->_getBidSelect($params);

        $mainSql = " FROM ({$bidSelect}) AS BS INNER JOIN property_listings AS P ON BS.property_id=P.property_id WHERE 1 ";

        if (!empty($params['sales_type'])) {
            $mainSql .= " AND LOWER(sales_type)='" . strtolower($params['sales_type']) . "'";
        }
        $sortingMap = array(
            'list_date' => 'ss_list_date DESC',
            'sales_type' => 'sales_type ASC',
            'state' => 'state ASC',
            'county' => 'county ASC',
            'city' => 'city ASC'
        );

        $mainSql .= " GROUP BY BS.property_id";


        $lastSenderClientId = "SELECT IFNULL(sender_client_id, '') FROM "
                . "bid_messages AS M INNER JOIN property_bids AS B ON M.bid_id=B.bid_id "
                . "WHERE B.property_id=BS.property_id ORDER BY M.created_time DESC LIMIT 1";

        $mainSql .= " ORDER BY ({$lastSenderClientId}) DESC";
        
        if (isset($sortingMap[$params['sort_listings_by']])) {
            $mainSql .= "," . $sortingMap[$params['sort_listings_by']];
        } else {
            $mainSql .= "," . $sortingMap['list_date'];
        }
        
        $selectQuery = "SELECT P.*, " .
                "MAX(BS.offer_price) AS highest_offer_price, " .
                "MIN(BS.offer_price) AS lowest_offer_price, " .
                "COUNT(BS.bid_id) AS total_bid_count, " .
                "IF(IFNULL(({$lastSenderClientId}), '')='', '', 1) AS has_unread_msg" .
                $mainSql;

        $query = $this->db->query("SELECT COUNT(T.property_id) AS cnt FROM ({$selectQuery}) AS T");
        $totalCount = $query->row()->cnt;

        if ($params['page_num'] && $params['page_rows_count']) {
            $pageCount = ceil($totalCount / $params['page_rows_count']);
            if ($pageCount > 0 && $pageCount < $params['page_num']) {
                $params['page_num'] = $pageCount;
            }
            $selectQuery .= " LIMIT " . ($params['page_num'] - 1) * $params['page_rows_count'] . ", " . $params['page_rows_count'];
        }

        $query = $this->db->query($selectQuery);
        $rows = $query->result_array();

        return array(
            'total_count' => $totalCount,
            'rows' => $rows
        );
    }

    /** get filtered bids for specific property */
    public function getBids($params) {
        $bidSelect = $this->_getBidSelect($params);
        
        
        $lastSenderClientId = "SELECT IFNULL(sender_client_id, '') FROM "
                . "bid_messages WHERE bid_id=B.bid_id ORDER BY created_time DESC LIMIT 1";

        $selectQuery = "SELECT B.*, CONCAT_WS(' ', C.first_name, C.last_name) AS clientname, "
                . "P.sales_type, P.strike_price, P.purchase_price, P.closed_time, P.closed_type, "
                . "IF(IFNULL(({$lastSenderClientId}), '')='', '', 1) AS has_unread_msg "
                . "FROM ({$bidSelect}) AS BS INNER JOIN property_bids AS B ON BS.bid_id=B.bid_id "
                . "INNER JOIN property_listings AS P ON B.property_id=P.property_id "
                . "INNER JOIN clients AS C ON B.client_id=C.client_id "
                . "WHERE 1 "
                . "ORDER BY B.created_time DESC";

        $query = $this->db->query($selectQuery);
        return $query->result_array();
    }

    public function getBidInfo($bidId){
        $sql = "SELECT * FROM " . self::T_NAME . " WHERE bid_id='{$bidId}'";
        $query = $this->db->query($sql);
        return $query->row_array();
    }
    
    /** change status of bid  */
    public function changeBidStatus($bidId, $newStatus) {
        if (!in_array($newStatus, array('opened', 'countered', 'accepted', 'declined', 'closed'))) {
            return;
        }

        //get bid info
        $bidInfo = $this->getBidInfo($bidId);
        $prevStatus = $bidInfo['status'];

        $this->db->update(self::T_NAME, array('bid_status' => $newStatus), array('bid_id' => $bidId));
        
        /*$this->load->model('property_m');
        $propertyInfo = $this->property_m->getPropertyInfo($bidInfo['property_id']);

        if ($newStatus == 'closed') {
            $this->property_m->closeSoldProperty($bidInfo['property_id']);
        } else if ($newStatus == 'accepted') {
            $this->property_m->goIntoNegotiating($bidInfo['property_id'], $propertyInfo['sales_type']);
        } else {
            if ($prevStatus == 'accepted') {
                $this->property_m->cancelNegotiating($property->property_id, $property->sales_type);
            }
        }*/
    }

}
