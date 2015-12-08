<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class propertyview_track_m extends CI_Model {

    const T_NAME = 'propertyview_tracks';
    
    public function trackView($clientId, $propertyId){
        $this->db->delete(self::T_NAME, array('client_id'=>$clientId, 'property_id'=>$propertyId));
        $this->db->insert(self::T_NAME, array(
            'client_id'=>$clientId, 
            'property_id'=>$propertyId,
            'last_view_time'=>date('Y-m-d H:i:s')
        ));
    }
    
    
    public function getVistedProperties($clientId, $limit = 0){
        $this->load->model('property_m', 'propertyModel');
        $sql = sprintf("SELECT L.property_id, L.last_view_time, R.npn_id , R.city , R.street_number , R.street , R.state , R.county , R.zip, R.sales_type FROM `%s` As L  JOIN %s as R on L.`property_id` = R.`property_id` WHERE L.client_id = '%d' ORDER BY last_view_time DESC", 
                    self::T_NAME, property_m::T_NAME, $clientId);
        if($limit != 0)
            $sql .= " LIMIT 0, {$limit}";
        return $rows = $this->db->query($sql)->result_array();
    }
}
