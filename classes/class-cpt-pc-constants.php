<?php
/**
 * http://pluginscorner.com
 *
 * @package instant-post-types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class CPT_PC_Const {

	/**
	 * Custom Post Type Translation Domain
	 */
	const T = 'cpt-pc-lang';

	/**
	 * Plugin Version
	 */
	const VERSION = '1';

	/**
	 * Database version
	 */
	const DB_VERSION = '0.2';

	/**
	 * Rest api namespace
	 */
	const REST_NAMESPACE = 'cpt-pc/v1';

	/**
	 * Plugins Corner API URL
	 */
	const API_URL = 'http://pluginscorner.com/api/';

	/**
	 * @var WP_Query|wpdb
	 */
	private static $wpdb;

	/**
	 * @var null
	 */
	private static $instance = null;

	/**
	 * CPT_PC_Const constructor.
	 */
	private function __construct() {
		global $wpdb;

		self::$wpdb = $wpdb;
	}

	/**
	 * Get the wpdb object (for performance we're )
	 *
	 * @return WP_Query|wpdb
	 */
	public static function get_wpdb() {
		if ( self::$instance == null ) {
			self::$instance = new self();
		}

		return self::$wpdb;
	}

	/**
	 * Get the plugin path
	 *
	 * @return string
	 */
	public static function plugin_path() {
		return plugin_dir_path( dirname( __FILE__ ) );
	}

	/**
	 * Get the plugin url
	 *
	 * @return string
	 */
	public static function plugin_uri() {
		return plugin_dir_url( dirname( __FILE__ ) );
	}

	/**
	 * wrapper over the wp_enqueue_style function
	 * it will add the plugin version to the style link if no version is specified
	 *
	 * @param             $handle
	 * @param string|bool $src
	 * @param array       $deps
	 * @param bool|string $ver
	 * @param string      $media
	 */
	public static function enqueue_style( $handle, $src = false, $deps = array(), $ver = false, $media = 'all' ) {
		if ( $ver === false ) {
			$ver = self::VERSION;
		}
		wp_enqueue_style( $handle, $src, $deps, $ver, $media );
	}

	/**
	 * Wrapper over the wp_enqueue_script function
	 * it will add the plugin version to the script source if no version is specified
	 *
	 * @param       $handle
	 * @param bool  $src
	 * @param array $deps
	 * @param bool  $ver
	 * @param bool  $in_footer
	 */
	public static function enqueue_script( $handle, $src = false, $deps = array(), $ver = false, $in_footer = false ) {
		if ( $ver === false ) {
			$ver = self::VERSION;
		}

		if ( defined( 'CPT_PC_DEBUG' ) && CPT_PC_DEBUG ) {
			$src = preg_replace( '#\.min\.js$#', '.js', $src );
		}

		wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
	}

	/**
	 * Autoload ALL FILES in a specific path
	 *
	 * @param string $path
	 * @param bool   $recursive
	 */
	public static function autoload( $path = '', $recursive = false ) {
		if ( ! empty( $path ) ) {
			$dir = new DirectoryIterator( $path );
			foreach ( $dir as $file_info ) {
				if ( $file_info->isDot() ) {
					continue;
				}

				if ( $recursive && $file_info->isDir() ) {
					$path = $file_info->getPathname();
					self::autoload( $path, true );
				} else {
					require_once( $file_info->getPathname() );
				}
			}
		}
	}

	/**
	 * Return the class name in the proper format
	 *
	 * @param        $file
	 * @param string $extension
	 *
	 * @return mixed|string
	 */
	public static function build_class_name( $file, $extension = '' ) {
		$base       = basename( $file, '.php' );
		$class_name = str_replace( 'class-', '', $base );
		$class_name = str_replace( 'cpt-pc-', '', $class_name );
		$class_name = str_replace( '-', '_', $class_name );
		$class_name = implode( '_', array_map( 'ucfirst', explode( '_', $class_name ) ) );
		$class_name = 'CPT_PC_' . $class_name;

		if ( ! empty( $extension ) ) {
			$class_name .= '_' . $extension;
		}

		return $class_name;
	}

	/**
	 * Processes an API response
	 *
	 * @param $response
	 *
	 * @return mixed
	 */
	public static function process_api_response( $response ) {
		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		$body = json_decode( $body );

		return $code === 200 ? $body->success : $body->error;
	}

}