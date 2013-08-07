<?php
/**
 * Proof model
 * 
 */
class Proof_model extends CI_Model {

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
	
	function get_username($userid){
		$this->db->where('id', $userid);
		$query = $this->db->get($this->user_table_name);
		if($query->num_rows() < 1){
			throw new Exception("User not found");
			
		}else{
			return $query->row()->username;
		}
		
	}
	
	/** Returns up to $numusers rows, starting from $offset
	 * 
	 * @param int
	 * @param int
	 * @return object[]
	 */
	function get_n_users($numusers, $offset){
		$this->db->where('username !=', $this->config->item('admin_username', 'proof'));
		return $this->db->get($this->user_table_name, $numusers, $offset);		
	}
	
	function get_profile($id){
		$this->db->where("id", $id);
		return $this->db->get($this->profile_table_name);
		
	}
	
	function get_num_users(){
		return $this->db->count_all($this->user_table_name);
		
	}
		
	function add_img_to_gallery($gallery_id, $imgid, $thumb, $original_filename){
		$data = array('gal_id' => $gallery_id, 'img_id' => $imgid, 'thumb' => $thumb, 'original_filename' => $original_filename);
		$this->db->insert($this->image_table_name, $data);
	}
	
	function get_user_images($galid){
		$imgdir = $this->config->item('rel_image_folder', 'proof');		
		$ds = '/';
		$imgs = array();
		$thumbs = array();
		$plainids = array();
		
		$this->db->order_by('order', 'asc');
		$this->db->where('gal_id', $galid);
		$query = $this->db->get($this->image_table_name);
		
		if($query->num_rows() > 0){
			foreach($query->result() as $row){
				array_push($imgs, site_url().$imgdir.$ds.$galid.$ds.$row->img_id);
				array_push($thumbs, site_url().$imgdir.$ds.$galid.$ds.$row->thumb);
				array_push($plainids, $row->img_id);
				
			}
			
		}
		return array('imgs' => $imgs, 'thumbs' => $thumbs, 'ids' => $plainids);
	}
	
	function gallery_exists($userid, $galleryname){
		$this->db->where('user_id', $userid);
		$this->db->where('gallery_name', $galleryname);
		$numres = $this->db->count_all_results($this->gallery_table_name);
		if($numres > 0){
			return TRUE;
		}else{
			return FALSE;
		}
		
	}
	
	function get_gallery_id($userid){
		$this->db->where('user_id', $userid);
		$query = $this->db->get($this->gallery_table_name);
		$numrows = $query->num_rows();
		if($numrows == 1){
			$gallery = $query->row();
			return $gallery->gal_id;
			
		}else if($numrows == 0){
			throw new Exception('Gallery name not found');			
		}else{
			throw new Exception('Multiple galleries by this name found');
		}
		
	}
	/*
	 * Creates a gallery. Uses the default sizes if they exist.
	 */
	function create_gallery($userid, $galleryname, $galtype){
		//insert into gallery table
		if($this->gallery_exists($userid, $galleryname)){
			throw new Exception("Gallery already exists for user ".$userid);
			
		}
		$data = array('user_id' => $userid, 'gallery_name' => $galleryname, 'gallery_type' => $galtype);
		$this->db->insert($this->gallery_table_name, $data);
		
		//insert default sizes into img_size table for this gallery
		$galid = $this->db->insert_id();
		$defsizes = $this->get_default_print_sizes();
		$galsizes = Array();
		foreach($defsizes as $size){
			array_push($galsizes, Array('size_val' => $size['size_val'], 'no_input' => $size['no_input'], 'price' => $size['price']));
			
		}
		$this->create_sizes($galid, $galsizes);
		
	}
	/*returns true if the given galid is an open gallery, otherwise false*/
	function proof_gallery_open($usrid){
		$this->db->where('user_id', $usrid);
		$this->db->where('gallery_name', 'proof');
		$this->db->where('open', true);
		$query = $this->db->get($this->gallery_table_name);
		if($query->num_rows() < 1){
			return false;
			
		}else{
			return $query->row()->open;
			
		}
		
	}
	
	function get_galleries(){
		$this->db->select('gal.gal_id, gal.gallery_name, gal.open, gal.user_id, gal.gallery_type');
		$this->db->from($this->gallery_table_name." gal");
		$query = $this->db->get();
		if($query->num_rows() < 1){
			throw new Exception('No galleries found');
			
		}
		$resarray = Array();
		foreach($query->result() as $row){
			$sample_images = $this->_get_sample_image_array($row->gal_id, 4);
			foreach($sample_images as &$img){
				$gallery_url =  site_url().$this->config->item('rel_image_folder', 'proof').'/'.$row->gal_id.'/';
				
				$img['thumb'] = $gallery_url.$img['thumb'];				
				$img['img_id'] = $gallery_url.$img['img_id'];
				
			}
			array_push($resarray, Array('gal_id' => $row->gal_id, 'gallery_name' => $row->gallery_name, 'user_id' => $row->user_id, 'gallery_type' => $row->gallery_type, 'open' => $row->open, 'sample_imgs' => $sample_images));
			
		}
		return $resarray;
	}
	
	function proof_gallery_open_by_galid($galid){
		$this->db->where('gal_id', $galid);
		$this->db->where('open', true);
		$query = $this->db->get($this->gallery_table_name);
		if($query->num_rows() < 1){
			return false;
			
		}else{
			return true;
		}
		
		
	}
	function get_user_id($galid){
		$this->db->where('gal_id', $galid);
		$this->db->select('user_id');
		$query = $this->db->get($this->gallery_table_name);
		if($query->num_rows() < 1){
			throw new Exception('Gallery not found.');
			
		}
		$userid = $query->row()->user_id;
		if(is_null($userid)){
			throw new Exception('No user for this gallery.');
		}
		return $userid;
		
	}


	function toggle_gallery_open($galid){
	
		$this->db->where('gal_id', $galid);
		
		//gotta pass in false to ensure that it doesn't try to escape it, which breaks the query (syntax is correct but it has no effect).
		$this->db->set('open','!`open`', false);
		$this->db->update($this->gallery_table_name);
		
	}
	/** Changes the order of the images in a gallery. 
	 * 
	 * @param int $galleryid
	 * @param array $neworder
	 */
	function change_gallery_order($galleryid, $neworder){
		for($i = 0; $i < count($neworder);  $i++){
			$this->db->where('img_id', $neworder[$i]);
			$this->db->where('gal_id', $galleryid);
			$data = array('order' => $i);
			$this->db->update($this->image_table_name, $data);
			
		}
	
	}
	
	

	/**
	 * Clears a print request.
	 * 
	 * @param string $imgid
	 * @param int $size_id
	 */
	function clear_print_request($imgid, $size_id){
		$this->db->delete($this->request_table_name, array('img_id' => $imgid, 'size_id' => $size_id));
		
	}
	
	/** Clears all current requests in a gallery
	 * 
	 * @param int $galid
	 */
	function clear_all_print_requests($galid){
		$this->db->select("req.img_id, sizes.size_id");
		$this->db->where("gal.gal_id", $galid);
		$this->db->from($this->gallery_table_name." gal");
		$this->db->join($this->image_table_name." img", "gal.gal_id = img.gal_id");
		$this->db->join($this->request_table_name." req", "img.img_id = req.img_id");
		$this->db->join($this->img_size_table_name." sizes", "req.size_id = sizes.size_id");
		
		$query = $this->db->get();
		
		if($query->num_rows() > 1){
			foreach($query->result() as $row){
				$this->clear_print_request($row->img_id, $row->size_id);
				
			}
			
		}
		
	}
	/**
	 * This function creates new default sizes. If the array member has a size_id, it will be used to update instead of creating a new size.
	 * The argument is an array of strings.
	 * 
	 * @param array $defaults
	 */
	function specify_default_sizes($defaults){
		
		//insert new defaults
		foreach($defaults as $d){
			$sizeid = $d->size_id;
			if(!empty($sizeid)){
				$this->db->where('size_id', $sizeid);
				$this->db->update($this->img_size_table_name, array('size_val' => $d->value, 'no_input' => $d->no_input, 'price' => $d->price));	
			}else{
				$this->db->insert($this->img_size_table_name, array('size_val' => $d->value, 'default' => 1, 'no_input' => $d->no_input, 'price' => $d->price));
			}
			
		}
		
	}
	/**
	 * This creates a set of print sizes for a particular gallery
	 * 
	 * @param int $galid
	 * @param array $sizes
	 */
	function create_sizes($galid, $sizes){
		
		//delete old sizes associated with this galid
		$this->db->delete($this->img_size_table_name, array('gal_id'=>$galid));
		//insert new sizes for this galid
		foreach($sizes as $size){
			$this->db->insert($this->img_size_table_name, 
					array('size_val' => $size->value, 'price' => $size->price, 'no_input'=> $size->no_input, 'gal_id' => $galid));
			
		}
	}
	/**
	 * Returns the print sizes for a particular gallery, and if none are found, the default sizes
	 * 
	 * @param int $galid
	 */
	function get_sizes($galid){
		$this->db->where('gal_id', $galid);
		$query = $this->db->get($this->img_size_table_name);
		if($query->num_rows() < 1){
			//return the defaults instead
			$this->db->where('default', 1);
			$query = $this->db->get($this->img_size_table_name);
		
		}
		$data = array();
		foreach($query->result() as $row){
			array_push($data, Array("size_id" => $row->size_id, "size_val" => $row->size_val, "no_input" => $row->no_input, "price" => $row->price));
		
		}
		return $data;
	}
	function get_default_print_sizes(){
		$this->db->where('default', true);
		$query = $this->db->get($this->img_size_table_name);
		if($query->num_rows() <= 0){
			throw new Exception('No default print sizes set');
		}
		
		$res = array();
		foreach($query->result() as $row){
			array_push($res, array('size_id'=>$row->size_id, 'size_val'=> $row->size_val, 'no_input' => $row->no_input, 'price' => $row->price));
			
		}
		
		return $res;
		
	}
	function clear_print_size($size_id){
		$this->db->delete($this->img_size_table_name, array('size_id' => $size_id));
		
	}
	
	/**
	 * Returns all print reqs for an image
	 * @param int $imgid
	 * @return multitype:string |multitype:
	 */
	function get_print_reqs($imgid){
		$this->db->where('img_id', $imgid);
		$this->db->where('submitted', false);
		$query = $this->db->get($this->request_table_name);
		$retarray = array();
		if($query->num_rows() > 0){
			foreach($query->result() as $row){
				array_push($retarray, array("size_id" => $row->size_id, "img_id" => $row->img_id, "num_req" => $row->num_req));
			}
			
		}else{
			return array("error" => "no_reqs");
			
		}
		
		return $retarray;
	}
	/*
	 * Returns true if there are unsubmitted print requests for a user
	 */
	function has_reqs($usr_id){
		$this->db->where('submitted', false);
		$this->db->where('user_id', $usr_id);
		$this->db->from($this->request_table_name." reqs");
		$this->db->join($this->image_table_name." imgs", "imgs.img_id = reqs.img_id");
		$this->db->join($this->gallery_table_name." gals", "gals.gal_id = imgs.gal_id");
		$query = $this->db->get();
		if($query->num_rows() < 1){
			return false;
			
		}else{
			return true;
		}
		
	}
	
	function submit_reqs($galid){
		$this->db->where('gal_id', $galid);
		
		$galquery = $this->db->get($this->image_table_name);
		if($galquery->num_rows() < 1){
			throw new Exception('No images found');
			
		}
		
		$this->db->where('gal_id', $galid);
		$query = $this->db->get($this->gallery_table_name);
		if($query->num_rows() < 1){
			throw new Exception('Gallery not found');
			
		}
		$userid = $query->row()->user_id;
		
		$this->db->insert($this->proof_print_order_table_name, Array('user_id'=>$userid));
		$data = array('submitted' => 1, 'order_id' => $this->db->insert_id());
		foreach($galquery->result() as $row){
			$this->db->where('img_id', $row->img_id);
			$this->db->where('submitted', false);
			$this->db->update($this->request_table_name, $data);
		}
		
	}
	
	/** This creates a new request for a particular image at a particular size if it does not already exist.
	 * Overwrites any old request for the same image at a particular print size.
	 *
	 * @param int $imgid
	 * @param int $size_id
	 * @param int $numreq
	 */
	function set_print_request($imgid, $size_id, $numreq){
		$result = $this->db->get_where($this->request_table_name, array('img_id' => $imgid, 'size_id' => $size_id, 'submitted'=>false));
		if($result->num_rows() < 1){
			$data = array(
					'img_id' => $imgid,
					'size_id' => $size_id,
					'num_req' => $numreq
			);
			return $this->db->insert($this->request_table_name, $data);
		}else{
			$data = array(
					'num_req' => $numreq
			);
			$this->db->where('img_id', $imgid);
			$this->db->where('size_id', $size_id);
			return $this->db->update($this->request_table_name, $data);
				
		}
	}
	
	/** This function returns a list of images that have been requested by id with thumbnail names and a list of the requests.
	 *  It does not return any requests that have been attached to an order.
	 * @param int $galid
	 * @throws Exception
	 * @return multitype:
	 */
	function get_req_images($galid){
		$this->db->distinct();
		$this->db->select('req.img_id, thumb');
		$this->db->where('gal_id', $galid);
		$this->db->where('submitted', false);
		$this->db->from($this->request_table_name." req");
		$this->db->join($this->image_table_name." img","req.img_id = img.img_id" );
		$query = $this->db->get();		
		
		if($query->num_rows() < 1){
			throw new Exception('No requested images on gallery with id = '.$galid );
			
		}
		
		$resarray = Array();
		foreach($query->result() as $row){
			$reqs = $this->get_print_reqs($row->img_id);
			$retreqs = Array();
			foreach($reqs as $req){
				$this->db->where('size_id', $req['size_id']);
				$query = $this->db->get($this->img_size_table_name);
				$resrow = $query->row();				
				$req['size'] = $resrow->size_val;
				$req['no_input'] = $resrow->no_input;
				$req['price'] = $resrow->price;
				array_push($retreqs, $req);
			}   
			
			array_push($resarray, Array('img_id' => $row->img_id, 'thumb' => $row->thumb, 'reqs' => $retreqs));	
			
		}
		return $resarray;
		
	}
	
	
	/**
	 * Deletes users and associated galleries/images
	 * 
	 * @param array $users
	 */
	function remove_users($users){
		$this->load->helper('file');
		foreach($users as $usrid){
			//Changes cascade due to the use of foreign keys
			$this->db->delete($this->user_table_name, array('id'=>$usrid));
			$userimgfolder = $this->config->item('image_folder', 'proof').'/'.$usrid;
			if(is_dir($userimgfolder)){
				delete_files($userimgfolder, TRUE);
				rmdir($userimgfolder);
				
			}				
		}
		
	}
	/**
	 * Returns a list of all users with active orders, along with a sample image for each user from the requests.
	 */
	
	function print_order_list($filled){	
		$this->db->distinct();
		$this->db->select('username, gal.user_id, gal.gal_id, gal.gallery_name, ord.timestamp, users.username, ord.order_id');
		$this->db->from($this->proof_print_order_table_name." ord");
		$this->db->join($this->user_table_name, "users.id = ord.user_id");
		$this->db->join($this->gallery_table_name." gal", "users.id = gal.user_id");
		$this->db->where('filled', $filled);
		
		
		$query = $this->db->get();
		
		$retarray = array();
		foreach($query->result() as $row){
			$sample_img = $this->_get_gallery_sample_image($row->gal_id);
			$gallery_url =  site_url().$this->config->item('rel_image_folder', 'proof').'/'.$row->gal_id.'/';
			$sample_img_url = $gallery_url.$sample_img['img_id'];
			$sample_thumb_url = $gallery_url.$sample_img['thumb'];
			array_push($retarray, array("username" => $row->username, 
				"user_id" => $row->user_id, 
				"gal_id" => $row->gal_id,
				"gallery_name" => $row->gallery_name,
				"timestamp" => $row->timestamp,
				"username" => $row->username,
				"order_id" => $row->order_id,
				"sample_image"=> array("url" => $sample_img_url, "thumb" => $sample_thumb_url)));
			
			
		}
		return $retarray;
	}
	
	
	function get_print_order($orderid){
		$this->db->distinct();
		$this->db->select('req.img_id, thumb, gal.user_id, gal.gallery_name, original_filename, gal.gal_id');
		$this->db->where('order_id', $orderid);
		$this->db->from($this->request_table_name." req");
		$this->db->join($this->image_table_name." img","req.img_id = img.img_id" );
		$this->db->join($this->gallery_table_name." gal", "img.gal_id = gal.gal_id");
		
		$query = $this->db->get();
		
		if($query->num_rows() < 1){
			throw new Exception("Order not found");
			
		}		
			
		$resarray = Array();
		foreach($query->result() as $row){
			$this->db->select("sizes.size_val, reqs.num_req, sizes.no_input, sizes.price");
			$this->db->from($this->request_table_name." reqs");
			$this->db->join($this->image_table_name." imgs", "imgs.img_id = reqs.img_id");
			$this->db->join($this->img_size_table_name." sizes", "reqs.size_id=sizes.size_id");
			
			$this->db->where('reqs.order_id', $orderid);
			$this->db->where('imgs.img_id', $row->img_id);
			$reqquery = $this->db->get();
			
			$reqarray = array();
			foreach($reqquery->result() as $arow){
				array_push($reqarray, array("size_val" => $arow->size_val, "num_req" => $arow->num_req, "no_input" => $arow->no_input, "price" => $arow->price));
			}
			
			$gallery_url =  site_url().$this->config->item('rel_image_folder', 'proof').'/'.$row->gal_id.'/';
			$img_url = $gallery_url.$row->img_id;
			$thumb_url = $gallery_url.$row->thumb;
			array_push($resarray, array("img_url" => $img_url, "thumb" => $thumb_url, "original_filename" => $row->original_filename, "reqs" => $reqarray));

			
		}
		return $resarray;
			
	}
	function toggle_order_filled($orderid){
		$this->output->enable_profiler(TRUE);
		$this->db->where("order_id", $orderid);
		$this->db->set('filled','!`filled`', false);
		$this->db->update($this->proof_print_order_table_name);
		
	}
	function order_filled($orderid){
		$this->db->where('order_id', $orderid);
		$this->db->select('filled');
		$query = $this->db->get($this->proof_print_order_table_name);
		return $query->row()->filled;
		
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
			return true;
		}
	}
	function _get_order_sample_image($orderid){
		$this->db->select('img.img_id, img.thumb');
		$this->db->from($this->image_table_name." img");
		$this->db->join($this->request_table_name." req", "img.img_id = req.img_id");
		$this->db->where('req.order_id', $orderid);
		$this->db->limit(1);
		$query = $this->db->get();
		$retval = array("img_id" => $query->row()->img_id, "thumb" => $query->row()->thumb);
		return $retval;
		
	}
	function _get_gallery_sample_image($galid){
		$this->db->select('img.img_id, img.thumb');
		$this->db->from($this->image_table_name." img");
		$this->db->join($this->request_table_name." req", "img.img_id = req.img_id");
		$this->db->where('img.gal_id', $galid);
		$this->db->limit(1);
		$query = $this->db->get();
		$retval = array("img_id" => $query->row()->img_id, "thumb" => $query->row()->thumb);
		return $retval;
		
		
	}
	function _get_sample_image_array($galid, $numlimit){
		$this->db->select('img.img_id, img.thumb');
		$this->db->from($this->gallery_table_name." gal");
		$this->db->join($this->image_table_name." img", "img.gal_id = gal.gal_id");
		
		$this->db->distinct();
		$this->db->where('gal.gal_id', $galid);
		$this->db->limit($numlimit);
		$query = $this->db->get();
		$retval = array();
		if($query->num_rows() < 1){
			return array();
			
		}
		foreach($query->result() as $row){
			array_push($retval, array("img_id" => $row->img_id, "thumb" => $row->thumb));
		}
		return $retval;
		
	}
	
	/*
	 * $this->db->select('username, gal.user_id, ord.order_id, img_id, size.size_val, reqs.num_req');
		$this->db->from($this->proof_print_order_table_name." ord");
		$this->db->join($this->user_table_name, "users.id = ord.user_id");
		$this->db->join($this->gallery_table_name." gal", "users.id = gal.user_id");
		$this->db->join($this->request_table_name." reqs", "reqs.order_id = ord.order_id");
		$this->db->join($this->img_size_table_name." size", "size.size_id=reqs.size_id");
		$query = $this->db->get();
	 */
}