<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Player extends MY_Controller {

	public function _remap($method){
		if (method_exists($this, $method)){
    		$this->$method();
  		} else {
    		$this->index($method);
  		}
	}

	public function index($artist_slug = NULL) {
		$this->session->unset_userdata('artists');
		$this->session->unset_userdata('songs');

		$this->load->library('geolocation');
		
		if(!$this->session->userdata('token')){
			$this->session->set_userdata('token', uniqid());
		}
		
		if(!$this->session->userdata('location')){
			$location = $this->geolocation->locate($this->input->ip_address());

            $this->session->set_userdata('location', json_encode($location));
        } else {
			$location = json_decode($this->session->userdata('location'));
		}

		$user_genres = array();

		if($this->session->userdata('loggedin') == TRUE){
			$userid = preg_replace("/^[a-zA-Z0-9]{13}/", "", $this->session->userdata('userid'));
			$userid = preg_replace("/[a-zA-Z0-9]{13}$/", "", $userid);

			$query = $this->db->get_where('tbl_users', array('id'=>$userid));
			$result = $query->result();

			if($result){
				$this->db->update('tbl_users', array('token'=>$this->session->userdata('token'), 'city'=>$location->city, 'region'=>$location->state, 'country'=>$location->country, 'latitude'=>$location->latitude, 'longitude'=>$location->longitude, 'last_ip'=>$this->input->ip_address()), array('id'=>$userid));
				
				$query = $this->db->query('SELECT genres FROM tbl_users WHERE id="'.$userid.'"');
				$user_genres = $query->result();
				$user_genres = explode(",", $user_genres[0]->genres);
			} else {
				$this->session->unset_userdata('loggedin');
	            $this->session->unset_userdata('userid');
			}
		}

		$query = $this->db->query('SELECT * FROM tbl_genres');
		$genres = $query->result();

		if(isset($artist_slug)){
			$artist = $this->db->get_where('tbl_artists', array('slug'=>$artist_slug))->result();
			$album = $this->db->get_where('tbl_albums', array('artist_id'=>$artist[0]->id, 'active'=>1), 1)->result();
			$song = $this->db->get_where('tbl_music', array('album_id'=>$album[0]->id, 'active'=>1), 1)->result();

			$this->load->view('player', array('genres'=>$genres, 'user_genres'=>$user_genres, 'artist_slug'=>$artist_slug, 'artist'=>$artist, 'song'=>$song, 'album'=>$album));
		} else {
			$this->load->view('player', array('genres'=>$genres, 'user_genres'=>$user_genres, 'artist'=>false, 'song'=>false, 'album'=>false));
		}
	}

	public function song($id = NULL){
        if($id){
            $query = $this->db->query("SELECT * FROM tbl_music WHERE id='$id'");
            $result = $query->result();
            $song = $result[0];

            $query = $this->db->query("SELECT * FROM tbl_artists WHERE id='$song->artist_id'");
            $result = $query->result();
            $artist = $result[0];

            $query = $this->db->query("SELECT * FROM tbl_albums WHERE id='$song->album_id'");
            $result = $query->result();
            $album = $result[0];

            $content['album_art'] = $this->config->item('base_url')."/content/artists/$artist->slug/albums/$album->slug/thumb.jpg";
            $content['song_title'] = $song->title;
            $content['song_url'] = APPPATH."../content/artists/$artist->slug/albums/$album->slug/$song->file";
            $content['artist_name'] = $artist->name;
            $content['album_name'] = $album->title;

            $this->load->view('song', $content);
        }
    }
}
