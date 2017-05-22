<?php

namespace quackenbushdev\devmail;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

use \DevMail;

/**
 * Class DevMailPostType
 * @package quackenbushdev\devmail
 * @version 1.0.2
 * @since 1.0.0
 */
class DevMailPostType {
	/**
	 * DevMailPostType constructor.
	 */
	public function __construct() {
		add_action('init', [$this, 'register_custom_post_type']);
		add_action('admin_init', [$this, 'register_dev_mail_meta_boxes']);
	}

	/**
	 * Register our custom dev_mail post type for wordpress use
	 *
	 * @since 1.0.0
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
				'public'              => false,
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
	 *
	 * @since 1.0.0
	 */
	public function register_dev_mail_meta_boxes() {
		add_meta_box(
			'dev_mail_view',
			__('Mail View'),
			[$this, 'view_message'],
			'dev_mail',
			'normal',
			'default',
			null
		);

		add_meta_box(
			'dev_mail_delivery',
			__('Mail Delivery'),
			[$this, 'deliver_message'],
			'dev_mail',
			'side',
			'default',
			null
		);
	}

	/**
	 * Display the email in our custom meta box.
	 *
	 * @since 1.0.0
	 */
	public function view_message() {
	    global $post;
	    $url = get_site_url(null, '/wp-json/dev_mail/v1/view/' . $post->ID . '?password=' . $post->post_password);
	    $stylePath = DevMail::get_asset_url('css/style.css');
	    wp_enqueue_style('devmail-admin', $stylePath);
		?>
            <script type="text/javascript">
                jQuery.ajax("<?php echo $url; ?>")
                    .done(function(result) {
                        var content = JSON.parse(result).content;

                        jQuery("#dev_mail-view-message-content").html(content);
                    });
            </script>
            <div id="dev_mail-view-message-content">
                <div class="loader"></div>
            </div>
		<?php
	}

	/**
	 * Display the email delivery box to forward the message outbound
     *
     * @since 1.0.2
	 */
	public function deliver_message() {
	    global $post;
	    $to = get_post_meta($post->ID, '_to', true);
	    ?>

        <form method="post" action="#">
            <label for="dev_mail_to">To:</label>
            <input id="dev_mail_to" name="dev_mail_to" type="text" />
            <input class="button button-primary button-large" type="submit" value="Send Message" />
        </form>
        <?php
    }
}
