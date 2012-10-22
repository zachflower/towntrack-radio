<?php
class Artists extends CI_Model {

    function __construct() {
        parent::__construct();
    }

	function random($artists_array = NULL, $user_id = NULL){
		if(isset($user_id)){
			$gquery = $this->db->query('SELECT genres FROM tbl_users WHERE id="'.$user_id.'"');
			$genres = $gquery->result();
			$genres = explode(",", $genres[0]->genres);
		
        	$dlquery = $this->db->get_where('tbl_dislikes', array('user_id'=>$user_id));
	        $dlresults = $dlquery->result();
	        $dlartists = array();
			$exclude = array();

	        foreach($dlresults as $dlresult){
	            $new_query = $this->db->query('SELECT artist_id FROM tbl_music WHERE id="'.$dlresult->song_id.'"');
	            $new_result = $new_query->result();
	            $dlartists[$new_result[0]->artist_id][] = $dlresult->song_id;
	        }

	        foreach($dlartists as &$dlartist){
	            $dlartist = array_unique($dlartist);
	        }

	        unset($dlartist);
	
			foreach($dlartists as $dlartist_id=>$dlsongs){
		        $this->db->where(array('artist_id'=>$dlartist_id));
		        $this->db->from('tbl_music');
		        $artists_total_songs = $this->db->count_all_results();
		        $total_dislikes = count($dlsongs);
				if($artists_total_songs == $total_dislikes){
					$exclude[] = $dlartist_id;
				}
			}
			
			$this->db->where_in('genre', $genres);
		}

		if(!isset($artists_array) || !is_array($artists_array)){
			/*if(isset($exclude) && count($exclude)>0){
				$this->db->where_not_in('id', $exclude);
			}

			$this->db->order_by('RAND()');
            $this->db->limit(1);
            $query = $this->db->get_where('tbl_artists', array('active'=>1));*/

			if(isset($exclude) && count($exclude)>0){
                $this->db->where_not_in('id', $exclude);
            }

            $this->db->order_by('RAND()');
            $this->db->limit(1);
            $query = $this->db->get_where('tbl_artists', array('active'=>1));
		} else {
			if(isset($exclude) && count($exclude)>0){
                $this->db->where_not_in('id', $exclude);
            }

			$this->db->where_not_in('id', $artists_array);
			$this->db->order_by('RAND()');
			$this->db->limit(1);
			$this->db->where(array('active'=>1));
			$query = $this->db->get('tbl_artists');
		}

		return($query->result());
	}

	function songCount($artist_id = NULL){
		$this->db->where(array('artist_id'=>$artist_id, 'active'=>1));
        $this->db->from('tbl_music');
        return($this->db->count_all_results());
	}

	function total(){
		$this->db->where(array('active'=>1));
        $this->db->from('tbl_artists');
        return($this->db->count_all_results());
	}

	function total_dislikes($user_id = NULL){
		if(isset($user_id)){
            $dlquery = $this->db->get_where('tbl_dislikes', array('user_id'=>$user_id));
            $dlresults = $dlquery->result();
            $dlartists = array();
            $exclude = array();

            foreach($dlresults as $dlresult){
                $new_query = $this->db->query('SELECT artist_id FROM tbl_music WHERE id="'.$dlresult->song_id.'"');
                $new_result = $new_query->result();
                $dlartists[$new_result[0]->artist_id][] = $dlresult->song_id;
            }

            foreach($dlartists as &$dlartist){
                $dlartist = array_unique($dlartist);
            }

            unset($dlartist);

            foreach($dlartists as $dlartist_id=>$dlsongs){
                $this->db->where(array('artist_id'=>$dlartist_id));
                $this->db->from('tbl_music');
                $artists_total_songs = $this->db->count_all_results();
                $total_dislikes = count($dlsongs);
                if($artists_total_songs == $total_dislikes){
                    $exclude[] = $dlartist_id;
                }
            }

			return(count($exclude));
        } else {
			return 0;
		}
	}
}
