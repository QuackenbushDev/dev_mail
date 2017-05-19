<?php

namespace quackenbushdev\devmail;

/**
 * Class DevMailPostType
 * @package quackenbushdev\devmailer
 */
class DevMailPostType {
	/**
	 * DevMailPostType constructor.
	 */
	public function __construct() {
		add_action('init', [$this, 'register_custom_post_type']);
		add_action('admin_init', [$this, 'register_mail_view_meta_box']);
	}

	/**
	 * Register our custom dev_mail post type for wordpress use
	 */
	public function register_custom_post_type() {
		register_post_type(
			'dev_mail',
			[
				'labels' => [
					'name'          => __('Dev Mail'),
					'singular_name' => __('Dev Mail'),
					'parent'        => __( 'Tools', 'Admin menu name' ),
					'menu_name'     => __( 'Dev Mail', 'Admin menu name' ),
				],
				'public'              => true,
				'show_ui'             => true,
				'map_meta_cap'        => true,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_in_menu'        => true,
				'hierarchical'        => false,
				'show_in_nav_menus'   => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => ['title'],
				'has_archive'         => false,
			]
		);
	}

	/**
	 * Register the meta box for our custom post that that'll be used to display the
	 * email content on the edit page.
	 */
	public function register_mail_view_meta_box() {
		add_meta_box(
			'dev_mail_view',
			__('Mail View'),
			[$this, 'view_message'],
			'dev_mail',
			'normal',
			'default',
			null
		);
	}

	/**
	 * Display the email in our custom meta box.
	 */
	public function view_message() {
		global $post;

		echo html_entity_decode($post->post_excerpt);
	}
}
