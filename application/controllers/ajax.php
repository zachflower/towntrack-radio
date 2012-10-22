<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ajax extends MY_Controller {

	public $loggedin = 0;
	public $userid = 0;

	function __construct(){
		parent::__construct();

		header('Content-Type: text/json; charset=utf-8');

		$this->loggedin = 0;
		$this->userid = 0;

        if($this->session->userdata('loggedin') == TRUE){
            $this->userid = preg_replace("/^[a-zA-Z0-9]{13}/", "", $this->session->userdata('userid'));
            $this->userid = preg_replace("/[a-zA-Z0-9]{13}$/", "", $this->userid);

            $query = $this->db->get_where('tbl_users', array('id'=>$this->userid));
            $result = $query->result();

            if($result){
                $this->loggedin = 1;
				$this->db->where('id', $this->userid);
	            $this->db->update('tbl_users', array('last_activity'=>date('Y-m-d H:i:s')));
            } else {
                $this->session->unset_userdata('loggedin');
                $this->session->unset_userdata('userid');
            }
        }
	}

	public function index(){
	}

	public function song($token = NULL, $artist_slug = NULL){
		if(!$token || $token != $this->session->userdata('token')){
			return false;
		}

        if($this->loggedin == 1){
			$user_id = $this->userid;
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
		} else {
			$user_id = NULL;	
		}

		$artists_array = $this->session->userdata('artists');

		$this->load->model('Artists');

        $artist = $this->Artists->random($artists_array, $user_id);

		if($artist_slug){
			$artist = $this->db->query("SELECT * FROM tbl_artists WHERE slug=? AND active=1", array($artist_slug))->result();
			if(!$artist){
				$artist = $this->Artists->random($artists_array, $user_id);
			}
		}

		$artists_array[] = $artist[0]->id;

		if($user_id){
			$artists_max = $this->Artists->total() - $this->Artists->total_dislikes($user_id);
		} else {
			$artists_max = 7;
		}
		
		if(count($artists_array) >= $artists_max-1){
			$artists_array = NULL;
			$artists_array[] = $artist[0]->id;
		}

		$this->session->set_userdata('artists', $artists_array);

		$songs_array = $this->session->userdata('songs');
		
		if(is_array($songs_array)){
			foreach($songs_array as &$sa){
				$sa = array_filter($sa);
			}
		}

		$this->load->model('Songs');
		
		$song = $this->Songs->random($artist[0]->id, $songs_array, $user_id);
		$total_songs = $this->Artists->songCount($artist[0]->id);

		if($total_songs != 1){
			$songs_array[$artist[0]->id][] = $song[0]->id;
		}

        if(isset($songs_array[$artist[0]->id])){
			if(isset($dlartists[$artist[0]->id])){
				$total_filter = $total_songs - count($dlartists[$artist[0]->id]);
			} else {
				$total_filter = $total_songs;
			}

			if(count($songs_array[$artist[0]->id]) == $total_filter){
				unset($songs_array[$artist[0]->id]);
			}
		}

        $this->session->set_userdata('songs', $songs_array);

        $query = $this->db->get_where('tbl_albums', array('id'=>$song[0]->album_id));
        $album = $query->result();

		if($this->loggedin == 1){
			$this->db->where(array('user_id'=>$this->userid, 'song_id'=>$song[0]->id));
			$this->db->from('tbl_likes');
			$liked = $this->db->count_all_results();
		} else {
			$liked = 0;
		}

		if(!$artist[0]->twitter){
			$artist[0]->twitter = $artist[0]->name;
		}

		$output['id'] = $song[0]->id;

		$soundcloud = $this->config->item('soundcloud');

		if(preg_match("/[^a-z]/i", $song[0]->soundcloud_url)){
			$output['song'] = "http://api.soundcloud.com/tracks/".$song[0]->soundcloud_url."/stream?client_id=".$soundcloud['id'];
			$output['soundcloud'] = 1;
		} else {
			$output['song'] = APPPATH."../content/artists/".$artist[0]->slug."/albums/".$album[0]->slug."/".$song[0]->file;
			$output['soundcloud'] = 0;
		}
	
		$output['liked'] = $liked;
		$output['profile'] = $this->config->item('base_url')."/content/artists/".$artist[0]->slug."/profile.jpg";
		$output['thumb'] = $this->config->item('base_url')."/content/artists/".$artist[0]->slug."/albums/".$album[0]->slug."/thumb.jpg";
		$output['info'] = array('artist'=>$artist[0]->name, 'twitter'=>$artist[0]->twitter, 'album'=>$album[0]->title, 'song'=>$song[0]->title, 'bio'=>$artist[0]->description, 'website'=>$artist[0]->website, 'artist_id'=>$artist[0]->id, 'location'=>ucwords(preg_replace("/-/", " ", strtolower($artist[0]->city))).', '.ucwords(preg_replace("/-/", " ", strtolower($artist[0]->state))), 'slug'=>$artist[0]->slug);
	
		$query = $this->db->get_where('tbl_albums', array('artist_id'=>$artist[0]->id));
        $album = $query->result();
	
		foreach($album as $a){
			if($a->url){
				$url = $a->url;
			} else {
				$url = NULL;
			}

            $output['albums'][$a->title] = array('title'=>$this->config->item('base_url')."/content/artists/".$artist[0]->slug."/albums/".$a->slug."/thumb.jpg", 'url'=>$url);
        }

        echo(json_encode($output));
	}
	
	public function toggleGenre($token = NULL, $slug = NULL){
		if(!$slug || !$token || $token != $this->session->userdata('token') || $this->loggedin == 0){
            return false;
        }

		$this->db->where('slug', $slug);
		$query = $this->db->get('tbl_genres');
		$genre = $query->result();
		$genre = $genre[0];

        $query = $this->db->query("SELECT genres FROM tbl_users WHERE id='".$this->userid."'");
        $genres = $query->result();
        $genres = explode(",", $genres[0]->genres);

		$found = FALSE;

		foreach($genres as $key=>$user_genre){
			if($user_genre == $genre->id){
				unset($genres[$key]);
				$found = TRUE;
				break;		
			}
		}
		
		if($found == FALSE){
			$genres[] = $genre->id;
		}
		
		sort($genres);
		
		$this->db->update('tbl_users', array('genres'=>implode(",", $genres)), array('id'=>$this->userid));
	}

	public function like($token = NULL, $id = NULL){
		if(!$id || !$token || $token != $this->session->userdata('token') || $this->loggedin == 0){
            return false;
        }

		$this->db->where('id', $id);
		$query = $this->db->get('tbl_music');
		$likes = $query->result();

        $this->db->where(array('user_id'=>$this->userid, 'song_id'=>$likes[0]->id));
        $this->db->from('tbl_likes');
        $total_likes = $this->db->count_all_results();

        if($total_likes == 0){
            $this->db->insert('tbl_likes', array('user_id'=>$this->userid, 'song_id'=>$likes[0]->id, 'token'=>$this->session->userdata('token'), 'create_date'=>date('Y-m-d')));

			$this->db->where('id', $id);
			$this->db->update('tbl_music', array('likes'=>$likes[0]->likes+1));
		}
	}

	public function unlike($token = NULL, $id = NULL){
        if(!$id || !$token || $token != $this->session->userdata('token') || $this->loggedin == 0){
            return false;
        }

		$this->db->where('id', $id);
		$query = $this->db->get('tbl_music');
		$likes = $query->result();

        $this->db->where(array('user_id'=>$this->userid, 'song_id'=>$likes[0]->id));
        $this->db->from('tbl_likes');
        $total_likes = $this->db->count_all_results();

        if($total_likes != 0){
            $this->db->delete('tbl_likes', array('user_id'=>$this->userid, 'song_id'=>$likes[0]->id));

	        $this->db->where('id', $id);
	        $this->db->update('tbl_music', array('likes'=>$likes[0]->likes-1));
		}
    }

	public function dislike($token = NULL, $id = NULL){
		if(!$id || !$token || $token != $this->session->userdata('token') || $this->loggedin == 0){
            return false;
        }

		$this->db->where('id', $id);
        $query = $this->db->get('tbl_music');
        $dislikes = $query->result();

		$this->db->where(array('user_id'=>$this->userid, 'song_id'=>$dislikes[0]->id));
        $this->db->from('tbl_likes');
        $total_dislikes = $this->db->count_all_results();

        if($total_dislikes == 0){
            $this->db->insert('tbl_dislikes', array('user_id'=>$this->userid, 'song_id'=>$dislikes[0]->id, 'token'=>$this->session->userdata('token'), 'create_date'=>date('Y-m-d')));

	        $this->db->where('id', $id);
	        $this->db->update('tbl_music', array('dislikes'=>$dislikes[0]->dislikes+1));
		}
	}

	public function undislike($token = NULL, $id = NULL){
        if(!$id || !$token || $token != $this->session->userdata('token') || $this->loggedin == 0){
            return false;
        }

        $this->db->where('id', $id);
        $query = $this->db->get('tbl_music');
        $dislikes = $query->result();

        $this->db->where(array('user_id'=>$this->userid, 'song_id'=>$dislikes[0]->id));
        $this->db->from('tbl_likes');
        $total_dislikes = $this->db->count_all_results();

        if($total_dislikes == 0){
            $this->db->delete('tbl_dislikes', array('user_id'=>$this->userid, 'song_id'=>$dislikes[0]->id));

	        $this->db->where('id', $id);
	        $this->db->update('tbl_music', array('dislikes'=>$dislikes[0]->dislikes-1));
		}
    }

	public function plays($token = NULL, $id = NULL){
		if(!$id || !$token || $token != $this->session->userdata('token')){
            return false;
        }

		$this->db->where('id', $id);
        $query = $this->db->get('tbl_music');
        $plays = $query->result();

        $this->db->where('id', $id);
        $this->db->update('tbl_music', array('plays'=>$plays[0]->plays+1));
		$this->db->insert('tbl_plays', array('user_id'=>$this->userid, 'song_id'=>$id, 'token'=>$this->session->userdata('token'), 'create_date'=>date('Y-m-d')));
	}

	public function skip($token = NULL, $id = NULL, $val = NULL){
        if(!$id || !$token || $token != $this->session->userdata('token')){
            return false;
        }

		if($val){
			$val = urldecode($val);
		}

        $this->db->where('id', $id);
        $query = $this->db->get('tbl_music');
        $skips = $query->result();

        $this->db->where('id', $id);
        $this->db->update('tbl_music', array('skips'=>$skips[0]->skips+1));
		$this->db->insert('tbl_skips', array('user_id'=>$this->userid, 'song_id'=>$id, 'token'=>$this->session->userdata('token'), 'create_date'=>date('Y-m-d'), 'val'=>$val));
    }

	public function shows($token = NULL, $id = NULL){
		if(!$id || !$token || $token != $this->session->userdata('token')){
			return false;
		}

		$this->db->where('id', $id);
        $query = $this->db->get('tbl_music');
        $music = $query->result();

		$this->db->where('id', $music[0]->artist_id);
		$query = $this->db->get('tbl_artists');
		$artist = $query->result();

		if(!isset($artist[0]->facebook)){
			echo(json_encode(array('status'=>0, 'message'=>'This artist has no upcoming shows.')));
			return;
		}

		$feed = 'https://api.facebook.com/method/events.get?uid='.$artist[0]->facebook.'&access_token=AAADB527kbl0BAFIwrAlZAXtPstkvxfCyscIi3t4XoLLwJtVcZCPsdUDewozlOJgbxr6riXxTPPpKUFkWFQK8H5WEJ7GwIZD&format=json&start_time='.time();

	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $feed);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
 		$feed = curl_exec($ch);
 		curl_close($ch);

		$tour = json_decode($feed, true);

		if(!is_array($tour) || count($tour) < 1){
			echo(json_encode(array('status'=>0, 'message'=>'This artist has no upcoming shows.')));
			return;
		}

		$shows = array();

		$key = 0;

		foreach($tour as $item){
			$location = '';
			
			if((is_array($item) && is_array($item['venue'])) && ($item['venue']['city'] != '' || $item['venue']['state'] != '')){
				$location = ' - ';
				if($item['venue']['city'] != ''){
					$location .= ucwords($item['venue']['city']);
					
					if($item['venue']['state'] != ''){
						$location .= ', ';
					}
					
					if($item['venue']['state'] != ''){
						$location .= ucwords($item['venue']['state']);
					}
				}
			} else {
				if(is_array($item) && is_array($item['venue']) && $item['venue']['longitude'] != '' && $item['venue']['latitude'] != ''){
					// get city/state from mapquest api
					$ch = curl_init();
	    			curl_setopt($ch, CURLOPT_URL, "http://www.mapquestapi.com/geocoding/v1/reverse?key=Fmjtd%7Cluu2n9a7l1%2Ca2%3Do5-hatlq&lat=".$item['venue']['latitude']."&lng=".$item['venue']['longitude']."&callback=renderReverse");
	    			curl_setopt($ch, CURLOPT_HEADER, 0);
	    			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 					curl_setopt($ch, CURLOPT_TIMEOUT, 10);
 					$reverse = curl_exec($ch);
 					curl_close($ch);
 					
 					$reverse = json_decode(preg_replace("/^renderReverse\(/", "", preg_replace("/\);$/", "", $reverse)), TRUE);
 					
 					if(isset($reverse['results'][0]['locations'][0]['adminArea5']) && isset($reverse['results'][0]['locations'][0]['adminArea3'])){
	 					$location = " - ".$reverse['results'][0]['locations'][0]['adminArea5'].", ".$reverse['results'][0]['locations'][0]['adminArea3'];
	 				}
				}
			}
			
			$date = date('D M d', $item['start_time']);
			$shows[$key]['title'] = $date." - ".$item['location'].$location;
			$shows[$key]['link'] = 'https://www.facebook.com/events/'.number_format($item['eid'], 0, '','').'/';
			$key++;
		}

		unset($item);

		echo(json_encode(array('status'=>1, 'message'=>'success', 'shows'=>$shows)));
	}
	
	public function videos($token = NULL, $id = NULL){
		if(!$id || !$token || $token != $this->session->userdata('token')){
			return false;
		}

		$this->db->where('id', $id);
        $query = $this->db->get('tbl_music');
        $music = $query->result();

		$this->db->where('id', $music[0]->artist_id);
		$query = $this->db->get('tbl_artists');
		$artist = $query->result();

		if(!isset($artist[0]->youtube)){
			echo(json_encode(array('status'=>0, 'message'=>'This artist has no videos.')));
			return;
		}

		$feed = 'https://gdata.youtube.com/feeds/api/users/'.$artist[0]->youtube.'/uploads?alt=json';

	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $feed);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
 		$feed = curl_exec($ch);
 		curl_close($ch);

		$tour = json_decode($feed, true);

		$videos = array();
		$i=0;
		
		if(isset($tour['feed']['entry'])){
			foreach($tour['feed']['entry'] as $video){
				if($artist[0]->youtube_filter){
					if(!preg_match("/".preg_quote($artist[0]->youtube_filter)."/i", $video['title']['$t'])){
						continue;
					}
				}
				
				$videos[$i]['title'] = $video['title']['$t'];
				$videos[$i]['url'] = $video['media$group']['media$content'][0]['url'].'&autoplay=1';
				$videos[$i]['thumb'] = $video['media$group']['media$thumbnail'][1]['url'];
				$i++;
				
				if($i==8){
					break;
				}
			}
		}
		
		if(count($videos) < 1){
			echo(json_encode(array('status'=>0, 'message'=>'This artist has no videos.')));
			return;
		}

		echo(json_encode(array('status'=>1, 'message'=>'success', 'videos'=>$videos)));
	}

	public function isLoggedIn(){
		$output = array('loggedin'=>$this->loggedin);

		echo(json_encode($output));
	}

	public function checkEmail($address = NULL){
		$this->load->library('user_agent');

		if ($this->agent->is_referral()){
		    $referrer = $this->agent->referrer();
		} else {
			$referrer = false;
		}

		if($address && $referrer && preg_match("/towntrack\.net/", $referrer)){
			$query = $this->db->query('SELECT COUNT(*) as taken FROM tbl_users WHERE email="'.urldecode($address).'"');
			$result = $query->result();
			$json['taken'] = $result[0]->taken;
		} else {
			$json['taken'] = 1;
		}

		echo(json_encode($json));
	}

	public function createAccount($token = NULL){
		if(!$token || $token != $this->session->userdata('token')){
			$success = 0;
            echo(json_encode(array('success'=>$success)));
            return;
		}

		$email = $this->input->post('email');
		$password = $this->input->post('password');
		$success = 0;

		if(!$email || !$password || !preg_match("/^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/", $email) || !preg_match("/[0-9]/", $password) || !preg_match("/[a-zA-Z]/", $password) || strlen($password) < 6){
			$success = 0;
			echo(json_encode(array('success'=>$success)));
			return;
		}

		$this->db->like('email', strtolower($email));
		$this->db->from('tbl_users');
		$total = $this->db->count_all_results();
	
		if($total == 0){
			$insert = $this->db->insert('tbl_users', array('email'=>strtolower($email), 'password'=>md5(sha1($password."stalingrad!")), 'activate_token'=>md5(strtolower($email).md5(sha1($password."stalingrad!"))), 'active'=>1, 'token'=>$token, 'create_date'=>date('Y-m-d'), 'last_login'=>date('Y-m-d H:i:s'), 'last_activity'=>date('Y-m-d H:i:s')));

			if($insert){
				$query = $this->db->query('SELECT id FROM tbl_users WHERE email="'.strtolower($email).'"');
				$insert_id = $query->result();
				$insert_id = $insert_id[0]->id;

				/*$config = array(
				      	'apikey' => '1664a2c59227493f771ff9e19d98e00f-us4',    // Insert your api key
			 			'secure' => FALSE   // Optional (defaults to FALSE)
  				);
  				$this->load->library('MCAPI', $config, 'mail_chimp');

				$this->mail_chimp->listSubscribe('1f7d13667b', $email);*/

				$this->session->unset_userdata('userid');
		        $this->session->unset_userdata('loggedin');
				$this->session->set_userdata('userid', uniqid().$insert_id.uniqid());
	            $this->session->set_userdata('loggedin', TRUE);
				$success = 1;
				echo(json_encode(array('success'=>$success, 'userid'=>$insert_id)));
	            return;
			}
		} else {
			$success = 0;
            echo(json_encode(array('success'=>$success)));
	        return;
		}
	}

	public function login($token = NULL){
		if(!$token || $token != $this->session->userdata('token')){
            $success = 0;
            echo(json_encode(array('success'=>$success)));
            $this->session->unset_userdata('loggedin');
			$this->session->unset_userdata('email');
			return;
        }

		$email = $this->input->post('email');
        $password = $this->input->post('password');
        $success = 0;

        if(!$email || !$password || !preg_match("/^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/", $email) || !preg_match("/[0-9]/", $password) || !preg_match("/[a-zA-Z]/", $password) || strlen($password) < 6){
            $success = 0;
            echo(json_encode(array('success'=>$success)));
            return;
        }
		
		$query = $this->db->get_where('tbl_users', array('email'=>$email, 'password'=>md5(sha1($password."stalingrad!"))));
        $user = $query->result();

		if($user){
			$this->session->set_userdata('userid', uniqid().$user[0]->id.uniqid());
	        $this->session->set_userdata('loggedin', TRUE);
			$this->db->where('id',$user[0]->id);
			$this->db->update('tbl_users', array('last_login'=>date('Y-m-d H:i:s')));
			$success = 1;
            echo(json_encode(array('success'=>$success)));
            return;
		} else {
			$success = 0;
            echo(json_encode(array('success'=>$success)));
            return;
		}
	}

	public function logout(){
        $this->session->unset_userdata('loggedin');
        $this->session->unset_userdata('userid');
        return;
	}

	public function checkmail(){
		echo(json_encode(array('success'=>false, 'user_id'=>$this->userid)));
	}
}
