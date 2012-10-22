<?php

class Geolocation {
	public $token = NULL;
	function __construct(){
		$this->token = '08b5f227e87c3399d802bac9407270deca5584b5face3f6093096e8b24f67b40';
	}
	
	public function locate($ip = NULL){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://api.ipinfodb.com/v3/ip-city/?key='.$this->token.'&ip='.$ip);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$response = curl_exec($ch);
		
		$info = explode(";", $response);
		
		$return['ip'] = $info[2];
		$return['cc'] = $info[3];
		$return['country'] = $info[4];
		$return['state'] = $info[5];
		$return['city'] = $info[6];
		$return['zip'] = $info[7];
		$return['latitude'] = $info[8];
		$return['longitude'] = $info[9];
		$return['timezone'] = $info[10];

		return($return);
	}
}
