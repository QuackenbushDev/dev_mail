<?php
/*
Plugin Name: Dev Mail
Plugin URI: https://github.com/QuackenbushDev/dev_mail/
Description: This plugin captures all system e-mails for testing without externally delivering any messages.
Version: 1.0.1
Author: Christopher Quackenbush
Author URI: http://christopher.quackenbush.me
License: MIT
*/

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

/**
 * Class DevMailer
 *
 * @version 1.0.2
 * @since 1.0.0
 */
class DevMail {
	/**
	 * This variable is automatically set at run-time to the location of the classes directory.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $include_directory = '';

	/**
	 * Template include directory for loading our mail view correctly.
	 *
	 * @var string
	 */
	private $plugin_directory = '';

	/**
	 * This array keeps a copy of all loaded classes for possible later use
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $loaded = [];

	/**
	 * Store all notices in an array for displaying as an admin notice.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $notices = [];

	/**
	 * Gets the current plugin instance, or initializes a new instance
	 *
	 * @since 1.0.1
	 * @return DevMail
	 */
	public static function get_instance()
	{
		static $instance = null;

		if ($instance === null) {
			$instance = new DevMail();
			$instance->init();
		}

		return $instance;
	}

	/**
	 * DevMail init function.
	 *
	 * @since 1.0.1
	 */
	public function init() {
		$dependencies = [
			'dev-mail-post-type.php' => quackenbushdev\devmail\DevMailPostType::class,
			'dev-mail-admin.php'     => quackenbushdev\devmail\DevMailAdmin::class,
			'dev-mail-rest.php'      => quackenbushdev\devmail\DevMailRest::class,
		];
		$this->plugin_directory = plugin_dir_path(__FILE__);

		$this->load_dependencies($dependencies);
		$this->bind_wp_mail();

		add_action('admin_notices', [$this, 'display_notices']);

		register_deactivation_hook(__FILE__, [$this, 'clear_all_messages']);

		if (version_compare(PHP_VERSION, '7.0', '<')) {
			$this->add_notice('error', 'PHP v7.0 or later is required for dev mail');
		}
	}

	/**
	 * Adds a notice to the notices array for rendering at a later stage
	 *
	 * @since 1.0.1
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
	 *
	 * @since 1.0.1
	 */
	public function display_notices() {
		foreach ($this->notices as $type => $notices) {
			foreach ($notices as $notice) {
				echo '<div class="' . $type . '"><p>Dev Mail: ' . $notice . '</p></div>';
			}
		}
	}

	/**
	 * Clears all stored e-mails in the system
	 *
	 * @since 1.0.1
	 */
	public function clear_all_messages() {
		$posts = get_posts(['post_type' => 'dev_mail']);

		foreach ($posts as $post_id) {
			wp_delete_post($post_id, true);
		}

		$this->add_notice('success', 'Cleared all dev messages');
	}

	/**
	 * Returns the current plugin directory
	 *
	 * @return string
	 */
	public static function get_plugin_path() {
		return self::get_instance()->plugin_directory;
	}

	public static function get_asset_url($path) {
		return plugins_url('assets' . DIRECTORY_SEPARATOR . $path, __FILE__);
	}

	/**
	 * Loads an array of dependencies that are required by the plugin.
	 *
	 * @somce 1.0.0
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
	 * @since 1.0.0
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
	 *
	 * @since 1.0.0
	 */
	private function bind_wp_mail() {
		if (!defined('wp_mail')) {
			function wp_mail($to, $subject, $message, $headers = '', $attachments = []) {
				wp_insert_post(
					[
						'post_title'    => $subject,
						'post_content'  => $message,
						'post_type'     => 'dev_mail',
						'post_status'   => 'publish',
						'post_password' => wp_generate_password(12, false),
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

DevMail::get_instance();