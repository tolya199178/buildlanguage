<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class favorite_m extends CI_Model {

    const T_NAME = 'property_favorites';

    /**
     * @param array $params
     *      client_id null|client id - if null, id of logged in client
     *      page_num int|null
     *      page_rows_count int|null
     * 
     * @return array
     *      total_count
     *      rows array
     */
    public function getProperties($params) {
        $this->load->model('property_m');

        $mainSql = " FROM " . property_m::T_NAME . " AS P INNER JOIN " . self::T_NAME . " AS F ON P.property_id=F.property_id"
                . " WHERE IFNULL(P.closed_time, '')='' ";

        if (empty($params['client_id'])) {
            $params['client_id'] = $this->session->userdata('client_id');
        }

        $mainSql .= " AND F.client_id='{$params['client_id']}'";

        $mainSql .= " ORDER BY F.created_time DESC";

        $query = $this->db->query("SELECT COUNT(F.property_id) AS cnt " . $mainSql);

        $totalCount = $query->row()->cnt;

        if ($params['page_num'] && $params['page_rows_count']) {
            $pageCount = ceil($totalCount / $params['page_rows_count']);
            if ($pageCount > 0 && $pageCount < $params['page_num']) {
                $params['page_num'] = $pageCount;
            }
            $mainSql .= " LIMIT " . ($params['page_num'] - 1) * $params['page_rows_count'] . ", " . $params['page_rows_count'];
        }

        $query = $this->db->query("SELECT P.*, F.created_time AS add_favorite_date " . $mainSql);
        $rows = $query->result_array();

        return array(
            'total_count' => $totalCount,
            'rows' => $rows
        );
    }

    public function addToFavorites($clientId, $propertyId) {
        $this->deleteFromFavorites($clientId, $propertyId);

        return $this->db->insert(self::T_NAME, array(
                    'client_id' => $clientId,
                    'property_id' => $propertyId,
                    'created_time' => date('Y-m-d H:i:s')
        ));
    }

    public function deleteFromFavorites($clientId, $propertyId) {
        return $this->db->delete(self::T_NAME, array('client_id' => $clientId, 'property_id' => $propertyId));
    }

    public function getFavoriteProperties($clientId, $limit = 0) {
        $this->load->model('property_m', 'propertyModel');
        $sql = sprintf("SELECT L.property_id, L.created_time, R.npn_id , R.city , R.street_number , R.street , R.state , R.county , R.zip, R.sales_type FROM `%s` As L  JOIN %s as R on L.`property_id` = R.`property_id` WHERE L.client_id = '%d' ORDER BY created_time DESC", self::T_NAME, property_m::T_NAME, $clientId);
        if ($limit != 0)
            $sql .= " LIMIT 0, {$limit}";
        return $rows = $this->db->query($sql)->result_array();
    }

}
