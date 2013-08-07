<?php
class Gallery_model extends CI_Model {

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
		$this->config->load('proof', TRUE);

		$ci =& get_instance();
		$this->user_table_name = $ci->config->item('db_table_prefix', 'tank_auth').$this->user_table_name;
		$this->profile_table_name = $ci->config->item('db_table_prefix', 'tank_auth').$this->profile_table_name;
		$this->load->database();
	}
	
	public function new_gallery($galname, $galtype){
		$data = array("gallery_name" => $galname, "gallery_type" => $galtype);
		$this->db->insert($this->gallery_table_name, $data);
		return $this->db->insert_id();
	}
	
	function delete_gallery($galid){
		if($userid = $this->gallery_has_user($galid)){
			$this->delete_user($userid);
		}
		
		$this->db->where('gal_id', $galid);
		$this->db->delete($this->gallery_table_name);
	}
	
	function delete_user($userid){
		$this->db->where('id', $userid);
		$this->db->delete($this->user_table_name);
		
	}
	
	function gallery_has_user($galid){
		$this->db->where("gal_id", $galid);
		$this->db->select("user_id");
		$query = $this->db->get($this->gallery_table_name);
		if($query->num_rows() < 1){
			throw new Exception("Gallery not found");
				
		}
		$res = $query->row()->user_id;
		if(is_null($res)){
			return false;
		}else{
			return $res;
		}
	}
	function add_img_to_gallery($gallery_id, $imgid, $thumb, $original_filename){
		$data = array('gal_id' => $gallery_id, 'img_id' => $imgid, 'thumb' => $thumb, 'original_filename' => $original_filename);
		$this->db->insert($this->image_table_name, $data);
		return $this->db->insert_id();
		
	}
	
	function get_gallery_type($galid){
		$this->db->where('gal_id', $galid);
		$this->db->select('gallery_type');
		$query = $this->db->get($this->gallery_table_name);
		if($query->num_rows() < 1){
			throw new Exception('Gallery not found');
			
		}
		return $query->row()->gallery_type;
	}
	
	function get_gallery_name($galid){
		$this->db->where('gal_id', $galid);
		$this->db->select('gallery_name');
		$query = $this->db->get($this->gallery_table_name);
		if($query->num_rows() < 1){
			throw new Exception('Gallery not found');
		}
		return $query->row()->gallery_name;
		
	}
	
	function attach_user($galid, $userid){
		$this->db->where('gal_id', $galid);
		$this->db->set('user_id', $userid);
		$this->db->update($this->gallery_table_name);
		
		
	}
	
	function gallery_open($galid){
		$this->db->select('open');
		$this->db->where('gal_id', $galid);
		$query = $this->db->get($this->gallery_table_name);
		return $query->row()->open;
	}
	
	function get_gallery_info($galid){
		$this->db->where('gal_id', $galid);
		$query = $this->db->get($this->gallery_table_name);
		
		if($query->num_rows() < 1){
			throw new Exception('Gallery not found');
			
		}
		$row = $query->row();
		return array("gallery_name" => $row->gallery_name, "gal_id" => $row->gal_id, "gallery_type" => $row->gallery_type, "user_id" => $row->user_id, "open" => $row->open);
		
	}
	
	function get_gallery_images($galid){
		$this->db->select('img.img_id, img.thumb');
		$this->db->from($this->gallery_table_name." gal");
		$this->db->where('gal.gal_id', $galid);
		$this->db->join($this->image_table_name." img", "gal.gal_id = img.gal_id");
		$this->db->order_by('img.order', "asc");
		$query = $this->db->get();
		
		if($query->num_rows() < 1){
			throw new Exception('No images found');
			
		}
		$resarray = array();
		foreach($query->result() as $row){
			$ds = '/';
			$imgdir = $this->config->item('rel_image_folder', 'proof');
			$url = site_url().$imgdir.$ds.$galid.$ds.$row->img_id;
			$thumb = site_url().$imgdir.$ds.$galid.$ds.$row->thumb;
			array_push($resarray, array("url" => $url, "thumb" => $thumb, "id" => $row->img_id));
		}			
		return $resarray;
	}
	
	function set_gallery_name($galid, $newname){
		$this->db->where('gal_id', $galid);
		$this->db->set('gallery_name', $newname);
		$this->db->update($this->gallery_table_name);
		
		
	}
}