<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Menu_model extends CI_Model {
    
    public function __construct()
    {
        parent::__construct();
    }

    public function getAllMenu($limit, $start)
    {
        return $this->db->get('menu', $limit, $start)->result_array();
    }
}
