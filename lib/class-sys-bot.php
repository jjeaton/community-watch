<?php
defined( 'ABSPATH' ) OR exit;
/**
 * Plugin: SysBot
 * Description: Creates and maintains the SysBot User (which has the role of "editor")
 * Author:      Franz Josef Kaiser
 */

# PUBLIC API #
function get_bot()
{
	$sys_bot = new sys_bot();
	$sys_bot = $sys_bot::init();
	$bot = get_user_by( 'email', $sys_bot->get_mail() );
	return $bot;
}

/**
 * Registers a bot user to map posts to
 * @author    Franz Josef Kaiser <wecodemore@gmail.com>
 * @copyright 2013 Franz Josef Kaiser
 * @license   MIT
 * @version   2013-01-25.1927
 */
class sys_bot
{
	/**
	 * @access protected
	 * @static
	 * @var    object Instance of the Class
	 */
	protected static $instance;

	/**
	 * @static
	 * @var    string Full Mailadress
	 */
	public static $mail = '';

	/**
	 * @var boolean
	 */
	 public $multibot = false;

	/**
	 * @TODO Change this value to your bots mail slug
	 * @var string Email Address
	 */
	public $name = 'cwbot';

	/**
	 * @TODO Change this value to your bots display/nick name
	 * @var string Nickname
	 */
	public $nick = 'CommunityWatchBot';

	/**
	 * @var object Debug Container
	 */
	public $debug;

	/**
	 * @static
	 * @return object|sys_bot
	 */
	public static function init()
	{
		null === self::$instance AND self::$instance = new self;
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct()
	{
		add_action( 'admin_init', array( $this, 'register_bot' ) );

		// if (
		// 	current_user_can( 'manage_options' )
		// 	AND ( defined( 'WP_DEBUG' ) AND WP_DEBUG )
		// )
		// 	add_action( 'shutdown', array( $this, 'debug' ) );
	}

	/**
	 * @TODO Shorten username of bot, make nicename work
	 * @return object WP_User or WP_Error on fail
	 */
	public function register_bot()
	{
		// Check if bot exists
		$has_user = username_exists( $this->get_mail() );
		if ( null !== $has_user )
		{
			$user = get_user_by( 'email', $this->get_mail() );
			if (
				$user->data->user_nicename === $this->nick
				AND $user->data->user_nicename === $this->nick
			) {
				return $user;
			}

			// Update Bot
			return wp_update_user(
				array(
					'ID'            => $user->ID,
					'nickname'      => $this->nick,
					'user_nicename' => $this->nick,
					'first_name'    => $this->nick
				)
			);
		}

		// Generate the password...
		$password = wp_generate_password( 30, false );
		// ...and create the user
		$user_id  = wp_create_user(
			$this->get_mail(),
			$password,
			$this->get_mail()
		);

		// Set the bots nickname
		wp_update_user(
			array(
				'ID'            => $user_id,
				'nickname'      => $this->nick,
				'user_nicename' => $this->nick,
				'first_name'    => $this->nick
			)
		);
		// Setup the bot
		$user = new WP_User( $user_id );
		// Set the bots role
		$user->set_role( 'editor' );

		return $this->debug = $user;
	}

	/**
	 * @return string
	 */
	public function set_mail()
	{
		return self::$mail = sprintf(
			'%s@%s',
			$this->get_name(),
			$this->get_domain()
		);
	}

	/**
	 * @return string
	 */
	public function get_mail()
	{
		empty( self::$mail ) AND self::$mail = $this->set_mail();
		return self::$mail;
	}

	/**
	 * @return string $domain
	 */
	public function get_domain()
	{
		if ( ! is_multisite() ) {
			$scheme  = 'http';
			$scheme .= is_ssl() ? 's' : '';
			$domain = str_replace( "{$scheme}://", '', get_option( 'siteurl' ) );
			return $domain;
		}

		// Get main domain
		$domain = get_current_site()->domain;
		is_subdomain_install() AND $domain = wpmu_current_site()->domain;

		// Get subdomain
		if ( $this->multi_bot )
		{
			$name_ext = "_".ltrim( PATH_CURRENT_SITE, '/' );
			if ( is_subdomain_install() )
			{
				$scheme  = 'http';
				$scheme .= is_ssl() ? 's' : '';
				$domain = str_replace( "{$scheme}://", '', get_option( 'home' ) );
			}
		}

		return $domain;
	}

	/**
	 * @return string
	 */
	public function get_name()
	{
		if ( ! is_multisite() )
			return sprintf(
				'%s%s',
				$this->name,
				get_option( 'site_url' )
			);

		return sprintf(
			'%s%s',
			$this->name,
			! is_subdomain_install()
				? "_".ltrim( PATH_CURRENT_SITE, '/' )
				: ''
		);
	}

	/**
	 * @TODO Fix Debugging
	 * @return string
	 */
	public function debug()
	{
		if ( is_wp_error( $this->debug ) )
			return printf(
				'<p>%s: %s</p>',
				$this->debug->get_error_code(),
				$this->debug->get_error_message()
			);

		return ! empty( $this->debug )
			? sprintf(
				'<pre>%s</pre>',
				print_r( $this->debug, true)
			)
			: ''
		;
	}
}
