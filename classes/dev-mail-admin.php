<?php

namespace quackenbushdev\devmail;

/**
 * Class DevMailAdmin
 * @package quackenbushdev\devmailer
 */
class DevMailAdmin {
	/**
	 * DevMailAdmin constructor.
	 */
	public function __construct() {
		add_filter('manage_dev_mail_posts_columns', [$this, 'list_view_modify_columns'], 10, 1);
		add_action('manage_dev_mail_posts_custom_column', [$this, 'list_view_populate_columns'], 10, 2);
	}

	/**
	 * Adds the required custom columns to the list view for dev_mail post type
	 *
	 * @param $columns
	 *
	 * @return array
	 */
	public function list_view_modify_columns($columns) {
		return [
			'cb'        => $columns['cb'],
			'title'     => __('Subject'),
			'to'        => __('To'),
			'user'      => __('User'),
			'sent_date' => __('Sent Date'),
			//'deliver'   => __('Deliver'),
		];
	}

	/**
	 * Populates the custom columns for the list view on dev_mail post type
	 *
	 * @param $column
	 * @param $post_id
	 */
	public function list_view_populate_columns($column, $post_id) {
		switch($column) {
			case "to":
				echo get_post_meta($post_id, '_to', true);
				break;

			case "user":
				$to = get_post_meta($post_id, '_to', true);
				$user = get_user_by('email', $to);

				if ($user !== false) {
					echo '<a href="' . get_edit_user_link($user->ID) . '">' . $user->display_name . '</a>';
				}
				break;

			case "sent_date":
				$post = get_post($post_id);
				echo $post->post_date;
				break;

			case "deliver":
				echo '<input type="button" value="Deliver Message">';
				break;
		}
	}
}