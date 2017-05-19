<?php
/*
Plugin Name: Dev Mail
Plugin URI: https://github.com/QuackenbushDev/dev_mail/
Description: This plugin captures all system e-mails for testing without externally delivering any messages.
Version: 1.0
Author: Christopher Quackenbush
Author URI: http://christopher.quackenbush.me
License: MIT
*/

use quackenbushdev\devmail\DevMailPostType;
use quackenbushdev\devmail\DevMailAdmin;

class DevMailer {
	/**
	 * This variable is automatically set at run-time to the location of the classes directory.
	 *
	 * @var string
	 */
	private $include_directory = '';

	/**
	 * This array keeps a copy of all loaded classes for possible later use
	 *
	 * @var array
	 */
	private $loaded = [];

	/**
	 * @var array
	 */
	private $notices = [];

	/**
	 * DevMailer constructor.
	 */
	public function __construct() {
		$dependencies = [
			'dev-mail-post-type.php' => DevMailPostType::class,
			'dev-mail-admin.php' => DevMailAdmin::class
		];

		$this->load_dependencies($dependencies);
		$this->bind_wp_mail();

		add_action('admin_notices', [$this, 'display_dev_mail_notices']);
	}

	/**
	 * Adds a notice to the notices array for rendering at a later stage
	 *
	 * @param string $type
	 * @param string $message
	 */
	public function add_notice($type, $message) {
		if (!array_key_exists($type, $this->notices)) {
			$this->notices[$type] = [];
		}

		$this->notices[$type][] = $message;
	}

	/**
	 * Render wp admin notices
	 */
	public function display_wp_mail_error() {
		foreach ($this->notices as $type => $notices) {
			foreach ($notices as $notice) {
				echo '<div class="' . $type . '"><p>Dev Mail: ' . $notice . '</p></div>';
			}
		}
	}

	/**
	 * Loads an array of dependencies that are required by the plugin.
	 *
	 * @param $dependencies
	 */
	private function load_dependencies($dependencies) {
		foreach($dependencies as $file => $class) {
			$this->load($file, $class);
		}
	}

	/**
	 * Dynamically registers a class
	 *
	 * @param string $file
	 * @param mixed $class
	 *
	 * @return mixed
	 */
	private function load($file, $class) {
		if (empty($this->include_directory)) {
			$this->include_directory = plugin_dir_path(__FILE__) . '/classes/';
		}

		if (array_key_exists($class, $this->loaded)) {
			return $this->loaded[$class];
		}

		require_once($this->include_directory . DIRECTORY_SEPARATOR . $file);
		$this->loaded[$class] = new $class();

		return $this->loaded[$class];
	}

	/**
	 * Rebind the native wp_mail to a function that creates a log in the system
	 */
	private function bind_wp_mail() {
		if (!defined('wp_mail')) {
			function wp_mail($to, $subject, $message, $headers = '', $attachments = []) {
				wp_insert_post(
					[
						'post_title'    => $subject,
						'post_excerpt'  => $message,
						'post_type'     => 'dev_mail',
						'post_status'   => 'publish',
						'post_password' => wp_generate_password(),
						'meta_input'    => [
							'_to'          => $to,
							'_headers'     => $headers,
							'_attachments' => $attachments,
						]
					]
				);

				return true;
			}
		} else {
			$this->add_notice('error', 'Unable to bind to wp_mail function due to already being defined.');
		}
	}
}

new DevMailer();
