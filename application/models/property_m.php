<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class property_m extends CI_Model {

    const T_NAME = 'property_listings';

    /**
     * get unclosed properties
     * 
     * @param array $params
     *      pid property_id
     *      sales_type
     *      minbeds
     *      minbaths
     *      minsqft
     *      state
     *      county array
     *      city
     *      address
     *      listings_category most_recent|all|special_offers
     *      sort_listings_by listed_date|sales_type|state|county
     *      page_num int|null
     *      page_rows_count int|null
     * 
     * @return array
     *      total_count
     *      rows array
     */
    public function getProperties($params) {

        if (empty($params['client_id'])) {
            $params['client_id'] = $this->session->userdata('client_id');
        }

        $this->load->model('favorite_m');
        $this->load->model('property_bid_m');

        $mainSql = " FROM " . self::T_NAME . " AS P WHERE 1 ";

        if ($params['closed'] === false) {
            $mainSql .= " AND IFNULL(closed_time, '')=''";
        }

        if ($params['closed'] === true) {
            $mainSql .= " AND IFNULL(closed_time, '')<>''";
        }

        if (!empty($params['pid'])) {
            $mainSql .= " AND property_id='{$params['pid']}'";
        }
        if (!empty($params['sales_type'])) {
            $mainSql .= " AND LOWER(sales_type) LIKE '" . strtolower($params['sales_type']) . "%'";
        }
        if (!empty($params['minbeds'])) {
            $mainSql .= " AND beds>='{$params['minbeds']}'";
        }
        if (!empty($params['minbaths'])) {
            $mainSql .= " AND baths>='{$params['minbaths']}'";
        }
        if (!empty($params['minsqft'])) {
            $mainSql .= " AND sqft>='{$params['minsqft']}'";
        }
        if (!empty($params['state'])) {
            $mainSql .= " AND state='{$params['state']}'";
        }
        if (!empty($params['county'])) {
            $params['county'] = (array) $params['county'];
            $mainSql .= " AND county IN ('" . join("','", $params['county']) . "')";
        }
        if (!empty($params['city'])) {
            $mainSql .= " AND LOWER(city) LIKE '%" . strtolower($params['city']) . "%'";
        }
        if (!empty($params['address'])) {
            $streetCol = "CONCAT(street_number, ' ', street, ',', city, ',', state, ' ', zip)";
            $mainSql .= " AND LOWER(" . $streetCol . ") LIKE '%" . strtolower($params['address']) . "%'";
        }
        if (!empty($params['search_keyword'])) {
            $params['search_keyword'] = strtolower($params['search_keyword']);
            $streetCol = "CONCAT(street_number, ' ', street, ',', city, ',', state, ' ', zip)";
            $mainSql .= " AND (npn_id='{$params['search_keyword']}' "
                    . "OR LOWER(" . $streetCol . ") LIKE '%{$params['search_keyword']}%' "
                    . "OR LOWER(city) LIKE '%{$params['search_keyword']}%' "
                    . "OR LOWER(sales_type) LIKE '%{$params['search_keyword']}%' "
                    . "OR zip='{$params['search_keyword']}' "
                    . "OR LOWER(state) LIKE '%{$params['search_keyword']}%')";
        }

        if ($params['listings_category'] == 'most_recent') {
            $mainSql .= " AND IFNULL(ss_list_date, '')<>'' AND ss_list_date>='" . date('Y-m-d', strtotime("-30 days")) . "'";
        } else if ($params['listings_category'] == 'special') {
            $mainSql .= " AND LOWER(property_status)='special'";
        } else if ($params['listings_category'] == 'myfavorites') {
            $mainSql .= " AND property_id IN (SELECT property_id FROM " . favorite_m::T_NAME . " WHERE client_id='{$params['client_id']}')";
        } else if ($params['listings_category'] == 'mybids') {
            $mainSql .= " AND property_id IN (SELECT DISTINCT(property_id) FROM " . property_bid_m::T_NAME . " WHERE client_id='{$params['client_id']}')";
        } else if ($params['listings_category'] === 'deleted') {
            $mainSql .= " AND IFNULL(closed_time, '')<>''";
        }

        $lastSenderAdminId = "SELECT IFNULL(sender_admin_id, '') FROM "
                . "bid_messages AS M LEFT JOIN " . property_bid_m::T_NAME . " AS B ON M.bid_id=B.bid_id "
                . "WHERE M.property_id=P.property_id AND (M.sender_client_id='{$params['client_id']}' OR M.to_client_id='{$params['client_id']}') ORDER BY M.created_time DESC LIMIT 1";

        $sortingMap = array(
            'list_date' => 'ss_list_date DESC',
            'sales_type' => 'sales_type ASC',
            'state' => 'state ASC',
            'county' => 'county ASC',
            'city' => 'city ASC',
            'created_time' => 'created_time DESC',
            'npn_id' => 'npn_id ASC',
            'sold_thru_web' => 'sold_thru_web DESC'
        );

        $extraStatusStr = "REPLACE(REPLACE(REPLACE(REPLACE(sales_type, 'reo_listed', ''), 'reo', ''), 'standard_sales', ''), 'short_sales', '')";

        //$mainSql .= " ORDER BY IFNULL(({$lastSenderAdminId}),'') DESC, {$extraStatusStr} DESC, ";
        $mainSql .= " ORDER BY ";
        if (!$params['ignore_msg_sort']) {
            $mainSql .= "IFNULL(({$lastSenderAdminId}),'') DESC, ";
        }
        if ($params['listings_category'] == 'most_recent') {
            $mainSql .= "{$extraStatusStr} DESC, ";
        }

        if (isset($sortingMap[$params['sort_listings_by']])) {
            $mainSql .= $sortingMap[$params['sort_listings_by']];
        } else {
            $mainSql .= $sortingMap['list_date'];
        }

        $query = $this->db->query("SELECT COUNT(property_id) AS cnt " . $mainSql);
        $totalCount = $query->row()->cnt;

        if ($params['page_num'] && $params['page_rows_count']) {
            $pageCount = ceil($totalCount / $params['page_rows_count']);
            if ($pageCount > 0 && $pageCount < $params['page_num']) {
                $params['page_num'] = $pageCount;
            }
            $mainSql .= " LIMIT " . ($params['page_num'] - 1) * $params['page_rows_count'] . ", " . $params['page_rows_count'];
        }

        $selectQuery = "SELECT *, " .
                "(SELECT COUNT(property_id) FROM " . favorite_m::T_NAME . " WHERE property_id=P.property_id AND client_id='{$params['client_id']}') AS fav_count, " .
                "(SELECT COUNT(bid_id) FROM " . property_bid_m::T_NAME . " WHERE property_id=P.property_id AND client_id='{$params['client_id']}') AS bid_count, " .
                "(SELECT COUNT(bid_id) FROM " . property_bid_m::T_NAME . " WHERE property_id=P.property_id AND client_id='{$params['client_id']}' AND bid_status='accepted') AS accepted_bid_count, " .
                "(SELECT COUNT(bid_id) FROM " . property_bid_m::T_NAME . " WHERE property_id=P.property_id AND client_id='{$params['client_id']}' AND bid_status='declined') AS declined_bid_count, " .
                "(SELECT COUNT(bid_id) FROM " . property_bid_m::T_NAME . " WHERE property_id=P.property_id AND client_id='{$params['client_id']}' AND bid_status='countered') AS countered_bid_count, " .
                "IF(IFNULL(({$lastSenderAdminId}), '')='', '', 1) AS has_unread_msg" .
                $mainSql;


        $query = $this->db->query($selectQuery);
        $rows = $query->result_array();

        return array(
            'total_count' => $totalCount,
            'rows' => $rows
        );
    }

    public function getLastUpdatedTime() {
        $sql = "SELECT MAX(created_time) AS val FROM " . self::T_NAME;
        $query = $this->db->query($sql);
        return $query->row()->val;
    }

    public function getSavedCounties() {
        $sql = "SELECT DISTINCT(county) AS val FROM " . self::T_NAME;
        $query = $this->db->query($sql);
        $rows = $query->result_array();

        function fetch_col_counties($a) {
            return $a['val'];
        }

        return array_map('fetch_col_counties', $rows);
    }

    public function getPropertyInfo($propertyId) {
        static $infos;
        
        if(!isset($infos)){
            $infos = array();
        }
        
        if(!isset($infos[$propertyId])){
            $sql = "SELECT * FROM " . self::T_NAME . " WHERE property_id='{$propertyId}'";
            $query = $this->db->query($sql);
            $infos[$propertyId] = $query->row_array();
        }
        
        return $infos[$propertyId];
    }

    /** close property with final check */
    public function closeSoldProperty($propertyId) {
        $this->closeProperty($propertyId, 'sold');
    }

    /** change sales_type of specific property to negotiating */
    public function goIntoNegotiating($propertyId, $salesType = null) {
        if (is_null($salesType)) {
            $info = $this->getPropertyInfo($propertyId);
            $salesType = $info['sales_type'];
        }
        $newSalesType = '';
        if (strpos($salesType, 'short_sales') !== false) {
            $newSalesType = 'short_sales_negotiating';
        }
        if (strpos($salesType, 'reo') !== false) {
            $newSalesType = 'reo_negotiating';
        }
        if (strpos($salesType, 'standard_sales') !== false) {
            $newSalesType = 'standard_sales_negotiating';
        }
        if ($newSalesType) {
            $this->db->update(self::T_NAME, array('sales_type' => $newSalesType), array('property_id' => $propertyId));
        }
    }

    public function cancelNegotiating($propertyId, $salesType = null) {
        if (is_null($salesType)) {
            $info = $this->getPropertyInfo($propertyId);
            $salesType = $info['sales_type'];
        }
        $newSalesType = '';
        if ($salesType == 'short_sales_negotiating') {
            $newSalesType = 'short_sales';
        }
        if ($salesType == 'reo_negotiating') {
            $newSalesType = 'reo';
        }
        if ($salesType == 'standard_sales_negotiating') {
            $newSalesType = 'standard_sales';
        }
        if ($newSalesType) {
            $this->db->update(self::T_NAME, array('sales_type' => $newSalesType), array('property_id' => $propertyId));
        }
    }

    /**
     * close property so that it can't be shown on client listings any more
     * 
     * @param int $propertyId - property id
     * @param string $closedType - sold or deleted
     */
    public function closeProperty($propertyId, $closedType='deleted') {
        $emailSubject = 'The property you are interested in is no longer available';
        $property = $this->getPropertyInfo($propertyId);
        
        $propertyAddress = $property['street_number'] . ' ' . $property['street'] . ', ' .
                $property['city'] . ' ' . $property['state'] . ' ' . $property['zip'];

        $clients = $this->db->query("SELECT * FROM clients WHERE "
                        . "client_id IN (SELECT DISTINCT(client_id) AS client_id FROM property_bids AS B WHERE property_id='{$propertyId}') "
                        . "OR client_id IN (SELECT DISTINCT(client_id) AS client_id FROM property_favorites AS F WHERE property_id='{$propertyId}') "
                        . "OR client_id IN (SELECT DISTINCT(client_id) AS client_id FROM property_notes AS N WHERE property_id='{$propertyId}') "
                        . "GROUP BY client_id")
                ->result_array();
        if (count($clients) > 0) {
            foreach ($clients as $client) {
                $emailContent = $client['first_name'] . ',<br/><br/>'
                        . 'Thank you for your interests for property located at <font color="red">' . $propertyAddress . '</font>. '
                        . 'Unfortunately, this property is no longer available, please contact Jennifer Hubbs at HMC Assets for any questions.';
                
                /* send_email(array(
                  'to' => $client['email_addr'],
                  'bcc'=>array('register@hmcassets.com', 'hmc@titan-cap.com', 'pip@hmcassets.com'),
                  'subject' => $emailSubject,
                  'content' => $emailContent,
                  'sales_type' => $property['sales_type']
                  )); */
            }
        }
        $this->db->update(self::T_NAME, array(
            'closed_type' => $closedType,
            'closed_time' => date('Y-m-d H:i:s')
                ), array('property_id' => $propertyId));
    }

    // add or update property information
    public function save($propertyInfo, $propertyId = null) {
        if(!isset($propertyInfo['created_time'])){
            $propertyInfo['created_time'] = date('Y-m-d H:i:s');
        }
        if (!empty($propertyId)) {
            $this->db->update(self::T_NAME, $propertyInfo, array('property_id' => $propertyId));
        } else {
            unset($propertyInfo['property_id']);
            $this->db->insert(self::T_NAME, $propertyInfo);
        }
    }

}
