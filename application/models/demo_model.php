<?php
class Demo_model extends CI_model{
	private $user_table_name = 'users';			// user accounts
	private $profile_table_name	= 'user_profiles';	// user profiles
	private $gallery_table_name = 'proof_galleries';
	private $image_table_name = 'proof_images';
	private $request_table_name = 'proof_requests';
	private $img_size_table_name = 'proof_print_sizes';
	private $proof_print_order_table_name = 'proof_print_orders';
	function __construct()
	{
		parent::__construct();
	
		$ci =& get_instance();
		$this->user_table_name = $ci->config->item('db_table_prefix', 'tank_auth').$this->user_table_name;
		$this->profile_table_name = $ci->config->item('db_table_prefix', 'tank_auth').$this->profile_table_name;
		$this->load->database();
	}
	
	function get_admin_id($username){
		$this->db->where('username', $username);
		$query = $this->db->get($this->user_table_name);
		if($query->num_rows() == 1) return $query->row()->id;
		return false;
		
	}
	
}