<?php
/**
 * http://pluginscorner.com
 *
 * @package instant-post-types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class CPT_PC_Autoloader {
	/**
	 * The path to autoload
	 *
	 * @var string
	 */
	private $plugin_path = null;

	/**
	 * The url to the plugin
	 *
	 * @var string
	 */
	private $plugin_url = null;

	/**
	 * CPT_PC_Autoloader constructor.
	 */
	public function __construct() {
		$this->plugin_path = plugin_dir_path( dirname( __FILE__ ) );
		/**
		 * Load the classes
		 */
		$dir = new DirectoryIterator( $this->plugin_path . 'classes' );

		foreach ( $dir as $file_info ) {
			if ( $file_info->isDot() || $file_info->isDir() ) {
				continue;
			}
			
			require_once( $file_info->getPathname() );
		}

		$this->plugin_url = CPT_PC_Const::plugin_uri();
		

		/**
		 * This should only be available on local
		 */
		if ( ! defined( 'ENVIRONMENT' ) && file_exists( $this->plugin_path . 'vendor/autoload.php' ) ) {
			require_once( $this->plugin_path . 'vendor/autoload.php' );
		}

		/**
		 * Enqueue the Utils Everywhere
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_utils' ) );

		/**
		 * Autoload all components if we're in the one plugin
		 */
		if ( defined( 'CPT_PC_PLUGIN' ) ) {
			$this->load();
		}

	}

	/**
	 * Autoload the inits (init.php / admin-init.php)
	 */
	private function load() {

		/**
		 * Load the components
		 */
		$admin_init = CPT_PC_Const::plugin_path() . 'admin' . DIRECTORY_SEPARATOR . 'admin-init.php';
		$init       = CPT_PC_Const::plugin_path() . 'public' . DIRECTORY_SEPARATOR . 'init.php';

		/**
		 * Instantiate the front end class
		 */
		if ( file_exists( $init ) && ! is_admin() ) {
			require_once( $init );

			$init_class = 'CPT_PC_Custom_Fields_Init';
			if ( class_exists( $init_class ) ) {
				new $init_class( 'custom-fields' );
			}
		}

		/**
		 * Instantiate the admin class
		 */
		if ( is_admin() && file_exists( $admin_init ) ) {
			require_once( $admin_init );

			$admin_class = 'CPT_PC_Custom_Fields_Admin_Init';
			if ( class_exists( $admin_class ) ) {
				new $admin_class( 'custom-fields', true );
			}
		}
	}

	/**
	 * Enqueue jquery, backbone, color pickers, sortable and the utils everywhere
	 */
	public function enqueue_utils() {
		/**
		 * Make sure we have jquery enqueued everywhere
		 */
		wp_enqueue_script( 'jquery' );

		/**
		 * For now backbone, color picker and sortable should only be used on the backend
		 */
		if ( is_admin() ) {
			wp_enqueue_script( 'backbone' );
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_script( 'jquery-ui-sortable', false, array( 'jquery' ) );

			CPT_PC_Const::enqueue_script( 'cpt-pc-utils', $this->plugin_url . 'js/dist/_util.min.js', array(
				'jquery',
				'backbone',
			), false, true );

			CPT_PC_Helpers::enqueue_dashboard();

			wp_localize_script( 'cpt-pc-utils', 'CPT_PCUtils', $this->get_localization() );

			add_action( 'admin_print_footer_scripts', array( $this, 'backbone_templates' ) );
		}
	}

	/**
	 * Include the backbone templates as scripts
	 */
	public function backbone_templates() {
		$template_path = $this->plugin_path . 'admin/templates/views';
		$templates     = $this->get_backbone_templates( $template_path, 'views' );

		foreach ( $templates as $tpl_id => $path ) {

			echo '<script type="text/template" id="' . $tpl_id . '">';
			include $path;
			echo '</script>';
		}
	}

	/**
	 * Gets the backbone templates so we can use them in views
	 *
	 * @param null   $dir
	 * @param string $root
	 *
	 * @return array
	 */
	public function get_backbone_templates( $dir = null, $root = 'template' ) {
		if ( null === $dir ) {
			$dir = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/backbone';
		}
		$folders   = scandir( $dir );
		$templates = array();
		foreach ( $folders as $item ) {
			if ( in_array( $item, array( '.', '..' ) ) ) {
				continue;
			}
			if ( is_dir( $dir . '/' . $item ) ) {
				$templates = array_merge( $templates, $this->get_backbone_templates( $dir . '/' . $item, $root ) );
			}
			if ( is_file( $dir . '/' . $item ) ) {
				$_parts               = explode( $root, $dir );
				$_truncated           = end( $_parts );
				$tpl_id               = ( ! empty( $_truncated ) ? trim( $_truncated, '/\\' ) . '/' : '' ) . str_replace( array(
						'.php',
						'.phtml',
					), '', $item );
				$tpl_id               = str_replace( array( '/', '\\' ), '-', $tpl_id );
				$templates[ $tpl_id ] = $dir . '/' . $item;
			}
		}

		return $templates;
	}


	/**
	 * Get data for ThriveUtils
	 *
	 * @return array
	 */
	public function get_localization() {
		$items = array(
			'nonce'    => wp_create_nonce( 'wp_rest' ),
			't'        => require CPT_PC_Const::plugin_path() . 'i18n.php',
			'is_free'  => defined( 'CPT_PC_FREE' ) ? (int) CPT_PC_FREE : 0,
			'routes'   => array(),
		);

		return $items;
	}
}
