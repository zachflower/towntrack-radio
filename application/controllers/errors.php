<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Errors extends MY_Controller {

	public function index() { }

	public function page_missing(){
		$this->load->view('404', NULL);
	}
}
