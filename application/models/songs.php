<?php
class Songs extends CI_Model {

    function __construct() {
        parent::__construct();
    }
    
	function random($artist_id = NULL, $songs_array = NULL, $user_id = NULL){
		$this->db->where(array('artist_id'=>$artist_id, 'active'=>1));
        $this->db->from('tbl_music');
        $total_songs = $this->db->count_all_results();

		if(isset($user_id)){
			$dlquery = $this->db->get_where('tbl_dislikes', array('user_id'=>$user_id));
        	$dlresults = $dlquery->result();
        	$dislikes = array();

        	foreach($dlresults as $dlresult){
        	    $dislikes[] = $dlresult->song_id;
        	}

			$dislikes = array_unique($dislikes);
		}

		if(is_array($songs_array)){
			foreach($songs_array as &$sa){
	        	$sa = array_filter($sa);
			}
		}

		unset($sa);

        if(!isset($songs_array[$artist_id]) || $total_songs == 1){
			if(isset($dislikes) && count($dislikes)>0){
				$this->db->where_not_in('id',$dislikes);
			}

            $this->db->order_by('RAND()');
            $this->db->limit(1);

            $query = $this->db->get_where('tbl_music', array('artist_id'=>$artist_id, 'active'=>1));
        } else {
			if(isset($dislikes) && count($dislikes)>0){
	            $this->db->where_not_in('id', $dislikes);
	        }

			$this->db->where_not_in('id', $songs_array[$artist_id]);

            $this->db->order_by('RAND()');
            $this->db->limit(1);
            $this->db->where(array('artist_id'=>$artist_id, 'active'=>1));

            $query = $this->db->get('tbl_music');
        }
		
		$results = $query->result();

		return($results);
	}
}
