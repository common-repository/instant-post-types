<?php
/**
 * http://pluginscorner.com
 *
 * @package instant-post-types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

abstract class CPT_PC_Helpers {

	/**
	 * @var WP_Query|wpdb
	 */
	protected static $wpdb;

	/**
	 * @var
	 */
	protected static $component_name;

	/**
	 * @var CPT_PC_Actions_Loader
	 */
	protected $loader;

	/**
	 * CPT_PC_Helpers constructor.
	 *
	 * @param string $component_name
	 */
	public function __construct( $component_name = '' ) {
		self::$wpdb = CPT_PC_Const::get_wpdb();

		$this->set_component( $component_name );

		/**
		 * Initialize the actions loader
		 */
		$this->loader = new CPT_PC_Actions_Loader();
		$this->add_actions();
		$this->loader->run();
	}

	/**
	 * Abstract actions and filters to be extended
	 */
	public function add_actions() {

	}

	/**
	 * @param $component_name
	 */
	protected function set_component( $component_name ) {
		self::$component_name = $component_name;
	}

	/**
	 * @param $path
	 */
	protected function load_helper( $path ) {
		$file = CPT_PC_Const::plugin_path() . $path;

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}

	/**
	 * Eqneuque Dashboard css and scripts / this should be available everywhere
	 */
	public static function enqueue_dashboard() {
		CPT_PC_Const::enqueue_script( 'cpt-pc-materialize', CPT_PC_Const::plugin_uri() . 'js/dist/materialize.min.js', array(
			'jquery',
			'backbone',
		), false, true );

		CPT_PC_Const::enqueue_style( 'cpt-pc-materialize', CPT_PC_Const::plugin_uri() . 'css/materialize.css' );
		CPT_PC_Const::enqueue_style( 'cpt-pc-animate', CPT_PC_Const::plugin_uri() . 'css/animate.css' );
		CPT_PC_Const::enqueue_style( 'cpt-pc-styles', CPT_PC_Const::plugin_uri() . 'css/styles.css' );
	}
}
