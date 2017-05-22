<?php

namespace quackenbushdev\devmail;

use \WP_REST_Request;

/**
 * Class DevMailRest
 * @package quackenbushdev\devmail
 *
 * @version 1.0.2
 * @since 1.0.1
 */
class DevMailRest {
	/**
	 * DevMailRest constructor.
	 */
	public function __construct() {
		add_action('rest_api_init', [$this, 'register_rest_routes']);
	}

	/**
	 * Register rest routes
	 *
	 * @since 1.0.1
	 */
	public function register_rest_routes() {
		$route_namespace = 'dev_mail/v1';

		register_rest_route(
			$route_namespace,
			'/view/(?P<id>\d+)',
			[
				'methods'  => 'GET',
				'callback' => [$this, 'load_email']
			]
		);
	}

	/**
	 * Returns the content of
	 *
	 * @since 1.0.1
	 * @param WP_REST_Request $request
	 *
	 * @return string
	 */
	public function load_email(WP_REST_Request $request) {
		$password = $request->get_param('password');
		$post = get_post($request->get_param('id'));

		if ($post->post_password === $password && $post->post_type === 'dev_mail') {
			return wp_json_encode(['content' => $post->post_content]);
		}

		return wp_json_encode(['content' => "Access Denied: Invalid post id or password."]);
	}
}