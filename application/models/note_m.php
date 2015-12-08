<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class note_m extends CI_Model {

    const T_NAME = 'property_notes';

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
    public function getMyNotes($params) {
        $this->load->model('property_m');

        $mainSql = " FROM " . self::T_NAME . " AS N LEFT JOIN " . property_m::T_NAME . " AS P ON N.property_id=P.property_id"
                . " WHERE IFNULL(P.closed_time, '')='' ";

        if (empty($params['client_id'])) {
            $params['client_id'] = $this->session->userdata('client_id');
        }

        $mainSql .= " AND N.client_id='{$params['client_id']}'";


        if (!empty($params['property_id'])) {
            $mainSql .= " AND N.property_id='{$params['property_id']}'";
        }

        //order claus
        $mainSql .= " ORDER BY N.created_time DESC";

        $query = $this->db->query("SELECT COUNT(N.note_id) AS cnt " . $mainSql);

        $totalCount = $query->row()->cnt;

        if ($params['page_num'] && $params['page_rows_count']) {
            $pageCount = ceil($totalCount / $params['page_rows_count']);
            if ($pageCount > 0 && $pageCount < $params['page_num']) {
                $params['page_num'] = $pageCount;
            }
            $mainSql .= " LIMIT " . ($params['page_num'] - 1) * $params['page_rows_count'] . ", " . $params['page_rows_count'];
        }

        $query = $this->db->query("SELECT P.property_id, P.npn_id, P.mls_id, P.street_number, P.street, P.city, P.state, P.zip, P.sales_type, N.* " . $mainSql);
        $rows = $query->result_array();

        return array(
            'total_count' => $totalCount,
            'rows' => $rows
        );
    }

    public function saveNote($clientId, $note, $propertyId = null, $noteId = null) {
        if (empty($propertyId)) {
            $propertyId = null;
        }

        $noteInfo = array(
            'client_id' => $clientId,
            'note' => $note
        );
        if (!is_null($propertyId)) {
            $noteInfo['property_id'] = $propertyId;
        }
        if (empty($noteId)) {
            $noteInfo['created_time'] = date('Y-m-d H:i:s');
            $this->db->insert(self::T_NAME, $noteInfo);
            $noteId = $this->db->insert_id();
        } else {
            $this->db->update(self::T_NAME, $noteInfo, array('note_id' => $noteId));
        }

        return $noteId;
    }

    public function deleteNote($noteId) {
        return $this->db->delete(self::T_NAME, array('note_id' => $noteId));
    }

    public function getClientNote($clientId, $limit = 0) {
        $this->load->model('property_m', 'propertyModel');
        $sql = sprintf("SELECT L.property_id, L.note, L.created_time, R.npn_id , R.city , R.street_number , R.street , R.state , R.county , R.zip FROM `%s` As L  JOIN %s as R on L.`property_id` = R.`property_id` WHERE L.client_id = '%d' ORDER BY created_time DESC", self::T_NAME, property_m::T_NAME, $clientId);
        if ($limit != 0)
            $sql .= " LIMIT 0, {$limit}";
        return $rows = $this->db->query($sql)->result_array();
    }

}
