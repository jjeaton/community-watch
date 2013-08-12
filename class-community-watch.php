<?php
/**
 * Community Watch
 *
 * @package   CommunityWatch
 * @author    Josh Eaton <josh@josheaton.org>
 * @license   GPL-2.0+
 * @link      http://www.josheaton.org/
 * @copyright 2013 Josh Eaton
 */

/**
 * Community Watch class
 *
 * @package CommunityWatch
 * @author  Josh Eaton <josh@josheaton.org>
 */
class CommunityWatch {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	protected $version = '1.0.0';

	/**
	 * Unique identifier for your plugin.
	 *
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'community-watch';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Post type name
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	const post_type = 'cw_content_report';

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Register post types and admin columns
		add_action( 'init',                                             array( $this, 'register_post_types'   )        );
		add_filter( 'manage_edit-'.self::post_type.'_columns',          array( $this, 'report_show_columns'   )        );
		add_action( 'manage_'.self::post_type.'_posts_custom_column',	array( $this, 'report_custom_columns' ), 10, 2 );

		// Load sysbot
		require_once( 'lib/class-sys-bot.php' );
		add_action( 'plugins_loaded', array( 'sys_bot', 'init' ), 30 );

		// Add the options page, menu item, and settings
		add_action( 'admin_menu',     array( $this, 'add_plugin_admin_menu' ) );
		add_action( 'admin_init',     array( $this, 'register_settings'     ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes'        ) );

		// Load public-facing style sheet and JavaScript.
 		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_head',            array( $this, 'public_css'      ) );

		// Display 'report' link
		add_filter( 'the_content', array( $this, 'display_report_link' ) );

		// Add bbpress support if it exists
		$this->bbpress_support();

		// Filter post types
		add_filter( 'cw_post_type_options', array( $this, 'remove_bbpress_cpts' ) );

		// AJAX Reporting Handlers
		add_action( 'wp_ajax_cw_report_post',        array( $this, 'ajax_report_post' ) );
		add_action( 'wp_ajax_nopriv_cw_report_post', array( $this, 'ajax_report_post' ) );

		// Add report link shortcode
		add_shortcode( 'cw_report_link', array( $this, 'add_cw_report_link_shortcode' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
		// Call the plugin to add the post type
		CommunityWatch::get_instance();

		// Flush rewrites for CPT
		flush_rewrite_rules();
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {
		// Flush rewrite rules for CPT
		flush_rewrite_rules();
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'css/public.css', __FILE__ ), array(), $this->version );
	}

	/**
	 * Output CSS for report link in head
	 *
	 * (Use until more CSS is needed.)
	 *
	 * @since    1.0.0
	 */
	public function public_css() {
		?>
<style>
	.cw-report-link.icons:before {
		content: "";
		background-image: url(<?php echo plugins_url( 'img/tick-x.png', __FILE__ );?>);
		background-repeat: no-repeat;
		display: inline-block;
		width: 14px;
		height: 14px;
		margin-top: 3px;
		line-height: 14px;
		vertical-align: text-top;
	}
	.cw-report-link.icons.reported:before,
	#bbpress-forums div.bbp-reply-content a.cw-report-link.icons.reported:before {
		background-position: 0px -14px;
	}
</style>
	<?php
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'js/public.js', __FILE__ ), array( 'jquery' ), $this->version );
		// Add nonce and ajaxurl for custom JS
		wp_localize_script( $this->plugin_slug . '-plugin-script', 'CWReportAJAX', array(
			'ajaxurl'	     => admin_url( 'admin-ajax.php' ),
			'nonce'		     => wp_create_nonce( 'cw_report_nonce' ),
			'reported_text'    => __( 'Reported', $this->plugin_slug )
			) );
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		/*
		 * Add plugin options page
		 */
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Community Watch Settings', $this->plugin_slug ),
			__( 'Community Watch', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

	}

	/**
	 * Adds the metabox container
	 *
	 * @since 1.0.0
	 */
	public function add_meta_boxes() {
		add_meta_box(
					 'cw_report_meta'
					, __( 'Report Details', $this->plugin_slug )
					, array( &$this, 'render_report_meta_boxes' )
					, self::post_type
					, 'advanced'
					, 'high'
				);
	}

	/**
	 * Callback for metaboxes on content_report CPT
	 *
	 * @since 1.0.0
	 */
	public function render_report_meta_boxes( $post ) {
		// Use nonce for verification
		wp_nonce_field( plugin_basename( __FILE__ ), 'cw_report_nonce' );

		// Get the field values
		$user_id          = get_post_meta( $post->ID, '_cw_reported_user_id', true );
		$reported_post_id = get_post_meta( $post->ID, '_cw_reported_post_id', true );

		if ( $user_id ) {
			$username = $this->get_username( $user_id );
			echo '<p>';
			echo '<label for="cw_reported_by">';
			_e( 'Reported By:', $this->plugin_slug );
			echo '</label> ';
			echo '<span id="cw_reported_by" name="cw_reported_by">'.esc_attr( $username ).'</span>';
			echo '</p>';
		}

		if ( $reported_post_id ) {
			echo '<p>';
			echo '<a id="cw_reported_post" name="cw_reported_post" href="'.get_permalink($reported_post_id).'">' . __('View content', $this->plugin_slug) . '</a>';
			echo '</p>';
		}
	}

	/**
	 * Register settings
	 *
	 * @since 1.0.0
	 */

	public function register_settings() {
		register_setting( 'cw_display', 'cw_display' );
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Build the report link
	 *
	 * Used in the shortcode and template tag
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function build_link() {
		global $post;

		$post_type = get_post_type( $post );

		if ( ! $post_type ) {
			return '';
		}

		$classes = 'cw-report-link';
		$post_type_obj = get_post_type_object( $post_type );

		$cw_display = get_option( 'cw_display' );

		if ( isset($cw_display['show_icons']) && $cw_display['show_icons'] ) {
			$classes .= ' icons';
		}

		// Build the link
		$link = '<a href="javascript:void(0);" data-user="'
				. wp_get_current_user()->ID
				. '" data-post-id="' . $post->ID
				. '" class="' . $classes . '">';
		$link .= sprintf( __('Report this %s', $this->plugin_slug),
				 strtolower($post_type_obj->labels->singular_name) );
		$link .= '</a>';

		return $link;
	}

	/**
	 * Display the 'report' link on a post
	 *
	 * Includes data attributes for the current user ID and post ID
	 *
	 * @since    1.0.0
	 */
	public function display_report_link( $content ) {
		global $post;
		$output = '';

		$post_type = get_post_type( $post );

		// Check if enabled for this post type
		if ( ! $this->should_display_link( $post_type ) ) {
			return $content;
		}

		// Build the link
		$link = $this->build_link();

		// Control where the link is displayed
		$cw_display = get_option( 'cw_display' );

		if ( isset( $cw_display['position'] ) ) {
			switch ( $cw_display['position'] ) {
				case 'top':
					$output = $link . $content;
					break;
				case 'bottom':
					$output = $content . $link;
					break;
				case 'both':
					$output = $link . $content . $link;
					break;
				default:
					$output = $content;
					break;
			}
		} else {
			$output = $content;
		}

		return $output;
	}

	/**
	 * Add bbpress reply support
	 *
	 * bbpress doesn't use the_content like other CPTs do, so we handle it differently
	 *
	 * @since 1.0.0
	 */
	public function bbpress_support() {
		// Check if enabled for this post type
		if ( ! $this->should_display_link( 'reply' ) ) {
			return;
		}

		// Get the CW display options
		$cw_display = get_option( 'cw_display' );

		// Control where the link is displayed
		if ( isset( $cw_display['position'] ) ) {
			switch ( $cw_display['position'] ) {
				case 'top':
					add_action( 'bbp_theme_before_reply_content', 'cw_report_link' );
					break;
				case 'bottom':
					add_action( 'bbp_theme_after_reply_content', 'cw_report_link' );
					break;
				case 'both':
					add_action( 'bbp_theme_before_reply_content', 'cw_report_link' );
					add_action( 'bbp_theme_after_reply_content', 'cw_report_link' );
					break;
			}
		}
	}

	/**
	 * Remove bbpress CPTs for Forums and Topics
	 *
	 * We don't support these in the plugin, so don't want to confuse users
	 *
	 * @since 1.0.0
	 */
	public function remove_bbpress_cpts( $types ) {
		// Check if bbpress is installed and activated, if so
		if ( function_exists( 'bbpress' ) ) {
			unset( $types['forum'] );
			unset( $types['topic'] );
		}

		return $types;
	}

	/**
	 * Create a [cw_report_link] shortcode
	 *
	 * Includes data attributes for the current user ID and post ID
	 *
	 * @since    1.0.0
	 */
	public function add_cw_report_link_shortcode( $atts ) {
		return $this->build_link();
	}

	/**
	 * Register custom post types
	 *
	 * @since 1.0.0
	 */
	public function register_post_types() {
		// Define local variable(s)
		$post_type = array();

		/** Content Reports ************************************************************/

		// Content Report labels
		$post_type['labels'] = array(
			'name'               => __( 'Content Reports',                   $this->plugin_slug ),
			'menu_name'          => __( 'Content Reports',                   $this->plugin_slug ),
			'singular_name'      => __( 'Content Report',                    $this->plugin_slug ),
			'all_items'          => __( 'Content Reports',                   $this->plugin_slug ),
			'add_new'            => __( 'New Content Report',                $this->plugin_slug ),
			'add_new_item'       => __( 'Create New Content Report',         $this->plugin_slug ),
			'edit'               => __( 'Edit',                              $this->plugin_slug ),
			'edit_item'          => __( 'Edit Content Report',               $this->plugin_slug ),
			'new_item'           => __( 'New Content Report',                $this->plugin_slug ),
			'view'               => __( 'View Content Report',               $this->plugin_slug ),
			'view_item'          => __( 'View Content Report',               $this->plugin_slug ),
			'search_items'       => __( 'Search Content Reports',            $this->plugin_slug ),
			'not_found'          => __( 'No Content Reports found',          $this->plugin_slug ),
			'not_found_in_trash' => __( 'No Content Reports found in Trash', $this->plugin_slug ),
			'parent_item_colon'  => __( 'Parent Content Report:',            $this->plugin_slug )
		);

		// Content Report rewrite
		$post_type['rewrite'] = array(
			'slug'       => 'content-report',
			'with_front' => false
		);

		// Content Report supports
		$post_type['supports'] = array(
			'title',
			'editor',
			'revisions'
		);

		// Register Content Report type
		register_post_type(
			self::post_type,
			array(
				'labels'              => $post_type['labels'],
				'rewrite'             => $post_type['rewrite'],
				'supports'            => $post_type['supports'],
				'description'         => __( 'Inappropriate Content User Reports', $this->plugin_slug ),
				'capability_type'     => 'post',
				'menu_position'       => null,
				'has_archive'         => 'content-reports',
				'exclude_from_search' => true,
				'show_in_nav_menus'   => false,
				'publicly_queryable'  => false,
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => 'index.php',
				'hierarchical'        => false,
				'query_var'           => false,
				'supports'            => array( 'title' ),
				'menu_icon'           => ''
			)
		);
	}

	/**
	 * Post type checkbox helper
	 *
	 * @since 1.0.0
	 */
	public function post_type_boxes( $cw_types, $prefix ) {

		if ( !is_array( $cw_types ) )
			$cw_types = array();

		// grab post types
		$args	= array(
			'public'	=> true
		);
		$output = 'objects';
		$types	= apply_filters( 'cw_post_type_options', get_post_types( $args, $output ) );

		$boxes	= '';

		// output loop of types
		if ( $types ) :
			foreach ( $types as $type ) :
				// type variables
				$name	= $type->name;
				$icon	= $type->menu_icon;
				$label	= $type->labels->name;
				// check for CPT in array
				$check	= !empty($cw_types) && in_array($name, $cw_types) ? 'checked="checked"' : '';
				// output checkboxes
				$boxes	.= '<input type="checkbox" name="'.$prefix.'[types][]" id="type-'.$name.'" value="'.$name.'" '.$check.' />';
				$boxes	.= ' <label for="type-'.$name.'">'.$label.'</label>';
				$boxes	.= '<br />';
			endforeach;
		endif;

		return $boxes;
	}

	/**
	 * Get enabled post types
	 *
	 * @since 1.0.0
	 */
	public function enabled_post_types() {
		$cw_display = get_option( 'cw_display' );

		// If enabled for types, return array of types
		if ( isset( $cw_display['types'] ) ) {
			return $cw_display['types'];
		}

		// return an empty array if no types are enabled
		return array();
	}

	/**
	 * Check if post type is enabled
	 *
	 * @since 1.0.0
	 */
	public function should_display_link( $post_type ) {
		$enabled_post_types = $this->enabled_post_types();

		if ( in_array( $post_type, $enabled_post_types ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Set custom column order for Content Reports
	 *
	 * @since 1.0.0
	 *
	 * @param  [array] $columns [admin columns]
	 *
	 * @return [array]          [admin columns]
	 */
	function report_show_columns( $columns ) {
		$columns = array(
				'cb'    => '<input type="checkbox" />',
				'title' => __( 'Title',       $this->plugin_slug ),
				'link'  => __( 'Link',        $this->plugin_slug ),
				'user'  => __( 'Reported By', $this->plugin_slug ),
				'date'  => __( 'Date',        $this->plugin_slug )
			);

		return $columns;
	}

	/**
	 * Render custom admin columns for Content Reports
	 *
	 * @since 1.0.0
	 */
	function report_custom_columns( $column_name, $post_id ) {
		global $post;
		switch ( $column_name ) {
			case 'link':
				$reported_post_id = get_post_meta( $post_id, '_cw_reported_post_id', true );
				if ( $reported_post_id ) {
					$link = get_permalink( $reported_post_id );
				} else {
					$link = '';
				}
				echo '<a class="link_col" href="' . esc_url( $link ) . '">'.__('View content', $this->plugin_slug).'</a>';
				break;
			case 'user':
				$user_id = get_post_meta( $post_id, '_cw_reported_user_id', true );
				$user    = $this->get_username( $user_id );
				echo '<span class="user_col">' . esc_attr( $user ) . '</span>';
				break;
		}
	}

	/**
	 * Handle AJAX post reporting
	 *
	 * @since 1.0.0
	 */
	function ajax_report_post() {
		check_ajax_referer( 'cw_report_nonce', 'nonce' );

		// TODO: Add check to see if user is logged in if needed.

		$post_id = $_POST['postid'];
		$user_id = $_POST['user'];
		$return  = array();

		// Check if we were given a post ID
		if( empty( $post_id ) ) {
			$return['success'] = false;
			$return['error']   = 'NO_POST_ID';
			$return['err_msg'] = 'No Post ID could be found.';
			echo json_encode( $return );
			die();
		}

		// Create a new report
		$title = get_the_title( $post_id );

		$username = $this->get_username( $user_id );
		// error_log ( 'Reported post: ' . $post_id . ': ' . $title . ' by user: ' . $user_id . ': ' . $username );

		$post_args = array(
			'post_title' => $title,
			'post_type'  => self::post_type,
			'post_status' => 'publish',
			'post_author' => get_bot()->ID // Get our bot ID as post author
		);

		$report_id = wp_insert_post( $post_args );

		if ( 0 != $report_id) {
			update_post_meta( $report_id, '_cw_reported_user_id', intval( $user_id ) );
			update_post_meta( $report_id, '_cw_reported_post_id', intval( $post_id ) );
		} else {
			$return['success'] = false;
			$return['error']   = 'FAILED_SUBMIT';
			$return['err_msg'] = 'Failed to submit report';
			echo json_encode( $return );
			die();
		}

		// Success, build the JSON to return
		$return['success'] = true;
		$return['message'] = 'reported ' . $post_id;

		// Notify admin
		$email_to = array( get_option('admin_email') );
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES); // Reverse esc_html for plain text
		$subject = sprintf( __('[%1$s Community Watch] Please review: "%2$s"', $this->plugin_slug), $blogname, $title );

		$message  = sprintf( __('Inappropriate content was reported on "%s". Please review.', $this->plugin_slug), $title ) . "\r\n\r\n";
		$message .= get_permalink($post_id) . "\r\n\r\n";
		$message .= sprintf( __('Reported By: %1$s', $this->plugin_slug), $username ) . "\r\n";

		// Send mail to all recipients
		foreach ( $email_to as $to )
			@wp_mail( $to, $subject, $message );

		echo json_encode( $return );
		die();
	}

	/**
	 * Helper to get username whether user is logged in or not
	 *
	 * @param  [int]     $user_id [WP user id]
	 * @return [string]           [WP user name]
	 */
	function get_username( $user_id ) {
		// Check if user is logged in
		if ( 0 != $user_id ) {
			$user = get_userdata( $user_id );
			$username = $user->user_login;
		} else {
			$username = __('Guest', $this->plugin_slug);
		}

		return $username;
	}

}

/**
 * Template tag for displaying link
 *
 * @since 1.0.0
 */
function cw_report_link() {
	echo CommunityWatch::get_instance()->build_link();
}
