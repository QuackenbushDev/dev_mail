<?php

class DevMailMailer {
	private $host;
	private $post;
	private $ssl;
	private $username;
	private $password;

	public function __construct() {
		$this->load_smtp_settings();
	}

	public function load_smtp_settings() {

	}

	public function send($id, $to) {
		$mailer = new PHPMailer();
	}
}