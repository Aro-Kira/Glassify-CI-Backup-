<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Role_request_model extends CI_Model {
    protected $table = 'role_requests';

    public function __construct()
    {
        parent::__construct();
    }

    public function create($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function last_request_within_days($user_id, $days = 90)
    {
        $threshold = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        $this->db->where('user_id', $user_id);
        $this->db->where('created_at >=', $threshold);
        return $this->db->get($this->table)->row();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }
}
