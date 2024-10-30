<?php
/**
 * http://pluginscorner.com
 *
 * @package instant-post-types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

abstract class CPT_PC_Init {
	/**
	 * @var string
	 */
	protected $component_name;

	/**
	 * @var string
	 */
	protected $include_dir;

	/**
	 * @var CPT_PC_Actions_Loader
	 */
	protected $loader;

	/**
	 * @var string
	 */
	protected $version;

	/**
	 * @var array
	 */
	protected $endpoints;

	/**
	 * @var
	 */
	protected $wpdb;

	/**
	 * CPT_PC_Init constructor.
	 *
	 * @param string $component_name
	 * @param bool   $is_admin_page
	 */
	public function __construct( $component_name = '', $is_admin_page = false ) {

		$this->wpdb = CPT_PC_Const::get_wpdb();

		$this->component_name = $component_name;
		$this->set_version( '1.0.0' );

		$this->include_dir = $is_admin_page ? 'admin' : 'public';
		$this->load_dependencies();
		$this->add_actions();
		$this->loader->run();

		$this->init();

	}

	/**
	 * Load Dependecies
	 */
	private function load_dependencies() {

		/**
		 * Load component helper file if exists and instantiate it's class
		 */
		$this->load_component_helper();

		/**
		 * Load the default files for admin EX: include (CPT_PC_Const::components_path($this->include_dir . '/xx.php', $this->component_name))
		 */
		$endpoints_dir = CPT_PC_Const::plugin_path() . $this->include_dir . '/endpoints';

		/**
		 * Initialize the actions loader
		 */
		$this->loader = new CPT_PC_Actions_Loader();
		/**
		 * Autoload the endpoint classes
		 */
		if ( file_exists( $endpoints_dir ) ) {

			$dir = new DirectoryIterator( $endpoints_dir );

			foreach ( $dir as $file_info ) {
				if ( $file_info->isDot() ) {
					continue;
				}

				/**
				 * Create the classname from the filename
				 * The files should be in the following format:  class-cpt-pc-{class-name}.php
				 */
				$file = CPT_PC_Const::build_class_name( $file_info->getFilename() );

				$this->endpoints[] = $file;

				require_once( $file_info->getPathname() );
			}

			$this->loader->add_action( 'rest_api_init', $this, 'create_initial_rest_routes' );
			$this->loader->run( 'rest_api_init' );

		}
	}

	/**
	 * Load component helper
	 */
	private function load_component_helper() {
		$this->load( 'helpers/class-cpt-pc-custom-post-helpers.php' );
		$helpers_class_name = CPT_PC_Const::build_class_name( 'custom-post-type', 'Helpers' );

		if ( class_exists( $helpers_class_name ) ) {
			new $helpers_class_name( $this->component_name );
		}
	}

	/**
	 * Abstract actions and filters to be extended
	 */
	public function add_actions() {

	}

	/**
	 * Abstract init to be extended
	 */
	public function init() {

	}

	/**
	 * Add the action for backbone templates
	 */
	public function load_backbone_templates() {
		/**
		 * include backbone script templates at the bottom of the page
		 */
		$this->loader->add_action( 'admin_print_footer_scripts', $this, 'backbone_templates' );

		$this->loader->run( 'admin_print_footer_scripts' );
	}

	/**
	 * Include the backbone templates as scripts
	 */
	public function backbone_templates() {
		$template_path = CPT_PC_Const::plugin_path() . $this->include_dir . '/templates/views';
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
	 * @return CPT_PC_Actions_Loader
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * @param CPT_PC_Actions_Loader $loader
	 */
	public function set_loader( $loader ) {
		$this->loader = $loader;
	}

	/**
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * @param string $version
	 */
	public function set_version( $version ) {
		$this->version = $version;
	}

	/**
	 * Get the route URL
	 *
	 * @param       $endpoint
	 * @param int   $id
	 * @param array $args
	 *
	 * @return string
	 */
	public function get_route_url( $endpoint, $id = 0, $args = array() ) {

		$url = get_rest_url() . CPT_PC_Const::REST_NAMESPACE . '/' . $endpoint;

		if ( ! empty( $id ) && is_numeric( $id ) ) {
			$url .= '/' . $id;
		}

		if ( ! empty( $args ) ) {
			add_query_arg( $args, $url );
		}

		return $url;
	}

	/**
	 * Instantiate the rest routes for ajax calls
	 */
	public function create_initial_rest_routes() {
		foreach ( $this->endpoints as $e ) {
			/** @var CPT_PC_REST_Controller $controller */
			$controller = new $e();
			$controller->register_routes();
		}
	}

	/**
	 * Loads a speciofic file within the component dir
	 *
	 * @param $path
	 */
	protected function load( $path ) {
		$file = CPT_PC_Const::plugin_path() . $path;

		if ( file_exists( $file ) ) {
			require( $file );
		}
	}

	/**
	 * Load a specific template file
	 *
	 * @param string $file
	 * @param array  $data
	 */
	protected function template( $file = '', $data = array() ) {
		require( CPT_PC_Const::plugin_path() . $this->include_dir . '/templates/' . $file );
	}

	/**
	 * wrapper over the wp_enqueue_script function
	 * it will add the plugin version to the script source if no version is specified
	 *
	 * @param             $handle
	 * @param string|bool $src
	 * @param array       $deps
	 * @param bool        $ver
	 * @param bool        $in_footer
	 */
	protected function enqueue_script( $handle, $src = false, $deps = array(), $ver = false, $in_footer = false ) {
		if ( $ver === false ) {
			$ver = $this->version;
		}

		if ( defined( 'CPT_PC_DEBUG' ) && CPT_PC_DEBUG ) {
			$src = preg_replace( '#\.min\.js$#', '.js', $src );
		}

		wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
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
	protected function enqueue_style( $handle, $src = false, $deps = array(), $ver = false, $media = 'all' ) {
		if ( $ver === false ) {
			$ver = $this->version;
		}
		wp_enqueue_style( $handle, $src, $deps, $ver, $media );
	}
}
